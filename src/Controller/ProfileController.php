<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Service\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $em, SessionUser $sessionUser, SluggerInterface $slugger): Response
    {
        $user = $sessionUser->requireLogin();
        if (!$user) return $this->redirectToRoute('app_login');

        $petRepository = $em->getRepository(Pet::class);
        $pet = null;

        $requestedPetId = (int)($request->request->get('pet_id') ?: $request->query->get('animal'));
        if ($requestedPetId > 0) {
            $pet = $petRepository->find($requestedPetId);
            if ($pet?->getOwner()?->getId() !== $user->getId()) {
                $pet = null;
            }
        }

        if (!$pet) {
            $pet = new Pet();
        }

        if ($request->isMethod('POST')) {
            $user
                ->setPrenom(trim((string)$request->request->get('prenom')))
                ->setNom(trim((string)$request->request->get('nom')))
                ->setEmail(strtolower(trim((string)$request->request->get('email'))))
                ->setVille(trim((string)$request->request->get('ville')) ?: null);

            $pet
                ->setOwner($user)
                ->setNom(trim((string)$request->request->get('pet_nom')))
                ->setEspece((string)$request->request->get('espece', 'chien'))
                ->setRace(trim((string)$request->request->get('race')) ?: null)
                ->setAge((int)$request->request->get('age'))
                ->setSexe((string)$request->request->get('sexe', ''))
                ->setVille($user->getVille())
                ->setBio(trim((string)$request->request->get('bio')) ?: null);

            $photo = $request->files->get('photo');
            if ($photo && $photo->isValid()) {
                $originalName = pathinfo((string)$photo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = strtolower((string)$slugger->slug($originalName ?: $pet->getNom()));
                $fileName = $safeName . '-' . uniqid() . '.' . ($photo->guessExtension() ?: 'jpg');
                $photo->move($this->getParameter('kernel.project_dir') . '/public/uploads', $fileName);
                $pet->setPhoto('uploads/' . $fileName);
            }

            $em->persist($pet);
            $em->flush();
            $this->addFlash('success', 'Profil sauvegarde.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'pet' => $pet,
            'pets' => $user->getPets(),
        ]);
    }
}
