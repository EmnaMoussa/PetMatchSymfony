<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/inscription', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = strtolower(trim((string)$request->request->get('email')));
            $password = (string)$request->request->get('password');

            if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Un compte existe deja avec cet email.');
                return $this->redirectToRoute('app_signup');
            }

            $user = (new User())
                ->setPrenom(trim((string)$request->request->get('prenom')))
                ->setNom(trim((string)$request->request->get('nom')))
                ->setEmail($email)
                ->setTelephone(trim((string)$request->request->get('telephone')) ?: null)
                ->setVille(trim((string)$request->request->get('ville')) ?: null)
                ->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));

            $pet = (new Pet())
                ->setOwner($user)
                ->setNom(trim((string)$request->request->get('pet_nom')))
                ->setEspece((string)$request->request->get('espece', 'chien'))
                ->setRace(trim((string)$request->request->get('race')) ?: null)
                ->setAge((int)$request->request->get('age'))
                ->setSexe((string)$request->request->get('sexe', ''))
                ->setVille($user->getVille())
                ->setBio(trim((string)$request->request->get('bio')) ?: null);

            $em->persist($user);
            $em->persist($pet);
            $em->flush();

            $request->getSession()->set('user_id', $user->getId());
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('auth/signup.html.twig');
    }

    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = strtolower(trim((string)$request->request->get('email')));
            $password = (string)$request->request->get('password');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !password_verify($password, $user->getPasswordHash())) {
                $this->addFlash('error', 'Email ou mot de passe incorrect.');
                return $this->redirectToRoute('app_login');
            }

            $request->getSession()->set('user_id', $user->getId());
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('auth/login.html.twig');
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('app_home');
    }
}
