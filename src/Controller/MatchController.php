<?php

namespace App\Controller;

use App\Entity\PetMatch;
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
        if (!$user) return $this->redirectToRoute('app_login');

        return $this->render('matches/index.html.twig', [
            'user' => $user,
            'matches' => $em->getRepository(PetMatch::class)->findBy(['user' => $user], ['id' => 'DESC']),
            'pets' => $user->getPets(),
        ]);
    }
}
