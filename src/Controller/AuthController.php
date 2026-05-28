<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class AuthController extends AbstractController
{
    #[Route('/inscription', name: 'app_signup', methods: ['GET', 'POST'])]
    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('signup', (string)$request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Formulaire expire, veuillez recommencer.');
                return $this->redirectToRoute('app_signup');
            }

            $email = strtolower(trim((string)$request->request->get('email')));
            $password = (string)$request->request->get('password');

            if (strlen($password) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caracteres.');
                return $this->redirectToRoute('app_signup');
            }

            if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Un compte existe deja avec cet email.');
                return $this->redirectToRoute('app_signup');
            }

            $petGender = strtolower(trim((string)$request->request->get('sexe')));
            if (!in_array($petGender, ['male', 'femelle'], true)) {
                $petGender = null;
            }

            $user = (new User())
                ->setFirstName(trim((string)$request->request->get('prenom')))
                ->setLastName(trim((string)$request->request->get('nom')))
                ->setEmail($email)
                ->setLocation(trim((string)$request->request->get('ville')) ?: null);

            $user->setPassword($passwordHasher->hashPassword($user, $password));

            $pet = (new Pet())
                ->setOwner($user)
                ->setName(trim((string)$request->request->get('pet_nom')))
                ->setType((string)$request->request->get('espece', 'chien'))
                ->setBreed(trim((string)$request->request->get('race')) ?: null)
                ->setAge($request->request->get('age') !== '' ? (int)$request->request->get('age') : null)
                ->setGender($petGender)
                ->setDescription(trim((string)$request->request->get('bio')) ?: null);

            $em->persist($user);
            $em->persist($pet);
            $em->flush();

            return $userAuthenticator->authenticateUser($user, $authenticator, $request);
        }

        return $this->render('auth/signup.html.twig', [
            'user' => null,
        ]);
    }

    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('auth/login.html.twig', [
            'user' => null,
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    #[Route('/logout', name: 'logout')]
    public function logout(): RedirectResponse
    {
        throw new \LogicException('Symfony Security intercepte cette route pour deconnecter.');
    }
}
