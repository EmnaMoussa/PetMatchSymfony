<?php

namespace App\Controller;

use App\Entity\PetMatch;
use App\Entity\Notification;
use App\Service\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MatchController extends AbstractController
{
    #[Route('/matches', name: 'app_matches')]
    public function matches(EntityManagerInterface $em, SessionUser $sessionUser): Response
    {
        $user = $sessionUser->requireLogin();
        if (!$user) {
            return $this->render('pages/access_denied.html.twig', [
                'user' => null,
            ]);
        }

        $notifications = $em->getRepository(Notification::class)->findBy(['user' => $user, 'readAt' => null]);
        foreach ($notifications as $notification) {
            $notification->setReadAt(new \DateTime());
        }
        if ($notifications) {
            $em->flush();
        }

        $matches = $em->getRepository(PetMatch::class)->findBy(['user' => $user], ['id' => 'DESC']);

        return $this->render('matches/index.html.twig', [
            'user' => $user,
            'matches' => $matches,
            'matchCount' => count($matches),
            'pets' => $user->getPets(),
        ]);
    }
}
