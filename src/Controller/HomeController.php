<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use App\Service\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em, SessionUser $sessionUser): Response
    {
        $pets = $em->getRepository(Pet::class)->findBy([], ['id' => 'DESC'], 4);

        return $this->render('home/index.html.twig', [
            'user' => $sessionUser->get(),
            'pets' => $pets,
        ]);
    }
}
