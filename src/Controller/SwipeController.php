<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetLike;
use App\Entity\PetMatch;
use App\Entity\PetSwipe;
use App\Entity\Notification;
use App\Service\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SwipeController extends AbstractController
{
    #[Route('/swipe', name: 'app_swipe')]
    public function swipe(Request $request, EntityManagerInterface $em, SessionUser $sessionUser): Response
    {
        $user = $sessionUser->requireLogin();
        if (!$user) {
            return $this->render('pages/access_denied.html.twig', [
                'user' => null,
            ]);
        }

        $userPets = $em->getRepository(Pet::class)->findBy(['owner' => $user], ['id' => 'ASC']);
        if (!$userPets) {
            $this->addFlash('error', 'Ajoutez au moins un animal avant de swiper.');
            return $this->redirectToRoute('app_profile');
        }

        $activePetId = (int)($request->request->get('owner_pet_id') ?: $request->query->get('animal'));
        $activePet = $userPets[0];
        foreach ($userPets as $userPet) {
            if ($userPet->getId() === $activePetId) {
                $activePet = $userPet;
                break;
            }
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('swipe_action', (string)$request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Formulaire expire, veuillez recommencer.');
                return $this->redirectToRoute('app_swipe', ['animal' => $activePet->getId()]);
            }

            $pet = $em->getRepository(Pet::class)->find((int)$request->request->get('pet_id'));
            $decision = (string)$request->request->get('decision');

            if (!$pet || $pet->getOwner()?->getId() === $user->getId() || !in_array($decision, ['like', 'skip'], true)) {
                $this->addFlash('error', 'Swipe invalide.');
                return $this->redirectToRoute('app_swipe', ['animal' => $activePet->getId()]);
            }

            $swipe = $em->getRepository(PetSwipe::class)->findOneBy([
                'ownerPet' => $activePet,
                'pet' => $pet,
            ]);

            if (!$swipe) {
                $swipe = (new PetSwipe())
                    ->setUser($user)
                    ->setOwnerPet($activePet)
                    ->setPet($pet);
                $em->persist($swipe);
            }

            $swipe->setDecision($decision);

            if ($decision === 'skip') {
                $this->removeRelation($em, PetLike::class, $activePet, $pet);
                $this->removeRelation($em, PetMatch::class, $activePet, $pet);
                $this->removeRelation($em, PetMatch::class, $pet, $activePet);
                $em->flush();

                $this->addFlash('success', $pet->getNom() . ' ne sera plus propose a ' . $activePet->getNom() . '.');
                return $this->redirectToRoute('app_swipe', ['animal' => $activePet->getId()]);
            }

            if (!$em->getRepository(PetLike::class)->findOneBy(['ownerPet' => $activePet, 'pet' => $pet])) {
                $em->persist((new PetLike())->setUser($user)->setOwnerPet($activePet)->setPet($pet));
            }

            $reverseSwipe = $em->getRepository(PetSwipe::class)->findOneBy([
                'ownerPet' => $pet,
                'pet' => $activePet,
                'decision' => 'like',
            ]);

            if ($reverseSwipe) {
                $currentUserMatch = $em->getRepository(PetMatch::class)->findOneBy(['ownerPet' => $activePet, 'pet' => $pet]);
                $otherUserMatch = $em->getRepository(PetMatch::class)->findOneBy(['ownerPet' => $pet, 'pet' => $activePet]);

                if (!$currentUserMatch) {
                    $currentUserMatch = (new PetMatch())->setUser($user)->setOwnerPet($activePet)->setPet($pet);
                    $em->persist($currentUserMatch);
                }

                if (!$otherUserMatch) {
                    $otherUserMatch = (new PetMatch())->setUser($pet->getOwner())->setOwnerPet($pet)->setPet($activePet);
                    $em->persist($otherUserMatch);
                }

                if (!$this->hasNotification($em, $user, $currentUserMatch)) {
                    $em->persist((new Notification())
                        ->setUser($user)
                        ->setPetMatch($currentUserMatch)
                        ->setType('match')
                        ->setMessage('Nouveau match entre ' . $activePet->getNom() . ' et ' . $pet->getNom()));
                }

                if ($pet->getOwner() && !$this->hasNotification($em, $pet->getOwner(), $otherUserMatch)) {
                    $em->persist((new Notification())
                        ->setUser($pet->getOwner())
                        ->setPetMatch($otherUserMatch)
                        ->setType('match')
                        ->setMessage('Nouveau match entre ' . $pet->getNom() . ' et ' . $activePet->getNom()));
                }

                $this->addFlash('success', $activePet->getNom() . ' a un nouveau match avec ' . $pet->getNom() . ' !');
            } else {
                $this->addFlash('success', 'Like enregistre. Le match apparaitra seulement si l autre proprietaire vous like aussi.');
            }

            $em->flush();

            return $this->redirectToRoute('app_swipe', ['animal' => $activePet->getId()]);
        }

        $swipes = $em->getRepository(PetSwipe::class)->findBy([
            'ownerPet' => $activePet,
        ]);

        $swipedPetIds = [];
        foreach ($swipes as $swipe) {
            if ($swipe->getPet()) {
                $swipedPetIds[] = $swipe->getPet()->getId();
            }
        }

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from(Pet::class, 'p')
            ->where('p.owner != :user')
            ->andWhere('p.type = :species')
            ->setParameter('user', $user)
            ->setParameter('species', $activePet->getType())
            ->setMaxResults(12);

        if ($activePet->getGender()) {
            $qb->addSelect('CASE WHEN p.gender IS NOT NULL AND p.gender <> :emptyGender AND p.gender <> :activeGender THEN 0 ELSE 1 END AS HIDDEN genderPriority')
                ->setParameter('emptyGender', '')
                ->setParameter('activeGender', $activePet->getGender())
                ->orderBy('genderPriority', 'ASC')
                ->addOrderBy('p.createdAt', 'DESC');
        } else {
            $qb->orderBy('p.createdAt', 'DESC');
        }

        if ($swipedPetIds) {
            $qb->andWhere('p.id NOT IN (:swipedPetIds)')
                ->setParameter('swipedPetIds', $swipedPetIds);
        }

        return $this->render('swipe/index.html.twig', [
            'user' => $user,
            'userPets' => $userPets,
            'activePet' => $activePet,
            'pets' => $qb->getQuery()->getResult(),
        ]);
    }

    private function removeRelation(EntityManagerInterface $em, string $entityClass, Pet $ownerPet, Pet $pet): void
    {
        $relation = $em->getRepository($entityClass)->findOneBy([
            'ownerPet' => $ownerPet,
            'pet' => $pet,
        ]);

        if ($relation) {
            $em->remove($relation);
        }
    }

    private function hasNotification(EntityManagerInterface $em, $user, PetMatch $match): bool
    {
        if (!$match->getId()) {
            return false;
        }

        return (bool)$em->getRepository(Notification::class)->findOneBy([
            'user' => $user,
            'petMatch' => $match,
            'type' => 'match',
        ]);
    }
}
