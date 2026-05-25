<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Pet;
use App\Service\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    #[Route('/chat/{id}', name: 'app_chat')]
    public function chat(Pet $pet, Request $request, EntityManagerInterface $em, SessionUser $sessionUser): Response
    {
        $user = $sessionUser->requireLogin();
        if (!$user) return $this->redirectToRoute('app_login');

        $receiver = $pet->getOwner();
        if (!$receiver) return $this->redirectToRoute('app_matches');

        if ($request->isMethod('POST')) {
            $content = trim((string)$request->request->get('message'));
            if ($content !== '') {
                $message = (new Message())->setSender($user)->setReceiver($receiver)->setContent($content);
                $em->persist($message);
                $em->flush();
            }
            return $this->redirectToRoute('app_chat', ['id' => $pet->getId()]);
        }

        $messages = $em->createQueryBuilder()
            ->select('m')
            ->from(Message::class, 'm')
            ->where('(m.sender = :user AND m.receiver = :receiver) OR (m.sender = :receiver AND m.receiver = :user)')
            ->setParameter('user', $user)
            ->setParameter('receiver', $receiver)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('chat/index.html.twig', [
            'user' => $user,
            'pet' => $pet,
            'messages' => $messages,
        ]);
    }
}
