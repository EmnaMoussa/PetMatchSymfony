<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Pet;
use App\Entity\PetMatch;
use App\Service\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat_index')]
    public function index(EntityManagerInterface $em, SessionUser $sessionUser): Response
    {
        $user = $sessionUser->requireLogin();
        if (!$user) {
            return $this->render('pages/access_denied.html.twig', [
                'user' => null,
            ]);
        }

        $firstMatch = $em->getRepository(PetMatch::class)->findOneBy(['user' => $user], ['id' => 'DESC']);
        if (!$firstMatch) {
            $this->addFlash('error', 'Vous devez avoir un match avant de discuter.');
            return $this->redirectToRoute('app_matches');
        }

        return $this->redirectToRoute('app_chat', ['id' => $firstMatch->getId()]);
    }

    #[Route('/chat/{id}', name: 'app_chat')]
    public function chat(int $id, Request $request, EntityManagerInterface $em, SessionUser $sessionUser): Response
    {
        $user = $sessionUser->requireLogin();
        if (!$user) {
            return $this->render('pages/access_denied.html.twig', [
                'user' => null,
            ]);
        }

        $match = $em->getRepository(PetMatch::class)->findOneBy([
            'id' => $id,
            'user' => $user,
        ]);

        if (!$match) {
            $this->addFlash('error', 'Conversation autorisee seulement avec un vrai match.');
            return $this->redirectToRoute('app_matches');
        }

        $pet = $match->getPet();
        $receiver = $pet->getOwner();
        if (!$receiver) {
            return $this->redirectToRoute('app_matches');
        }

        $conversationMatches = [$match];
        $reverseMatch = $em->getRepository(PetMatch::class)->findOneBy([
            'ownerPet' => $match->getPet(),
            'pet' => $match->getOwnerPet(),
        ]);

        if ($reverseMatch) {
            $conversationMatches[] = $reverseMatch;
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('chat_message', (string)$request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Formulaire expire, veuillez recommencer.');
                return $this->redirectToRoute('app_chat', ['id' => $match->getId()]);
            }

            $content = trim((string)$request->request->get('message'));
            if ($content !== '') {
                $message = (new Message())
                    ->setSender($user)
                    ->setReceiver($receiver)
                    ->setPetMatch($match)
                    ->setContent($content);

                $em->persist($message);

                $notificationMatch = $reverseMatch ?? $match;
                $em->persist((new Notification())
                    ->setUser($receiver)
                    ->setPetMatch($notificationMatch)
                    ->setMessage($user->getFullName() . ' vous a envoye un message.'));

                $em->flush();
            }

            return $this->redirectToRoute('app_chat', ['id' => $match->getId()]);
        }

        $unreadNotifications = $em->getRepository(Notification::class)->findBy([
            'user' => $user,
            'petMatch' => $conversationMatches,
            'readAt' => null,
        ]);
        foreach ($unreadNotifications as $notification) {
            $notification->setReadAt(new \DateTime());
        }
        if ($unreadNotifications) {
            $em->flush();
        }

        $messages = $em->createQueryBuilder()
            ->select('m')
            ->from(Message::class, 'm')
            ->where('m.petMatch IN (:matches)')
            ->setParameter('matches', $conversationMatches)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();

        $matches = $em->getRepository(PetMatch::class)->findBy(['user' => $user], ['id' => 'DESC']);

        return $this->render('chat/index.html.twig', [
            'user' => $user,
            'pet' => $pet,
            'match' => $match,
            'matches' => $matches,
            'messages' => $messages,
        ]);
    }
}
