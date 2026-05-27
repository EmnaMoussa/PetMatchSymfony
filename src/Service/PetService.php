<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Pet;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Pet Service
 * Handles pet profile management, image uploads, and queries
 * 
 * Implementation by: Emna Moussa
 */
class PetService
{
    private string $uploadDir = 'uploads/pets';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PetRepository $petRepository,
        private ImageService $imageService,
    ) {}

    /**
     * Create a new pet
     */
    public function createPet(
        User $owner,
        string $name,
        string $type,
        string $breed,
        ?int $age = null,
        ?string $gender = null,
        ?string $description = null,
    ): Pet {
        $pet = new Pet();
        $pet->setOwner($owner);
        $pet->setName($name);
        $pet->setType($type);
        $pet->setBreed($breed);
        $pet->setAge($age);
        $pet->setGender($gender);
        $pet->setDescription($description);

        $this->entityManager->persist($pet);
        $this->entityManager->flush();

        return $pet;
    }

    /**
     * Update pet details
     */
    public function updatePet(
        Pet $pet,
        string $name,
        string $type,
        string $breed,
        ?int $age = null,
        ?string $gender = null,
        ?string $description = null,
    ): Pet {
        $pet->setName($name);
        $pet->setType($type);
        $pet->setBreed($breed);
        $pet->setAge($age);
        $pet->setGender($gender);
        $pet->setDescription($description);
        $pet->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $pet;
    }

    /**
     * Upload pet image
     */
    public function uploadPetImage(Pet $pet, UploadedFile $file): string
    {
        // Delete old image if exists
        if ($pet->getImage()) {
            $this->imageService->deleteImage($pet->getImage());
        }

        // Upload new image
        $imagePath = $this->imageService->uploadImage($file, $this->uploadDir);
        $pet->setImage($imagePath);
        $pet->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $imagePath;
    }

    /**
     * Delete pet
     */
    public function deletePet(Pet $pet): bool
    {
        if ($pet->getImage()) {
            $this->imageService->deleteImage($pet->getImage());
        }

        $this->entityManager->remove($pet);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get user's pets
     */
    public function getUserPets(User $user): array
    {
        return $this->petRepository->findByOwner($user);
    }

    /**
     * Get available pets for swipe (exclude user's own and already swiped)
     */
    public function getAvailablePets(User $user, int $limit = 20): array
    {
        return $this->petRepository->findAvailableForSwipe($user, $limit);
    }

    /**
     * Find pets with filters
     */
    public function findWithFilters(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->petRepository->findWithFilters($filters, $limit, $offset);
    }

    /**
     * Get pet count for user
     */
    public function getPetCount(User $user): int
    {
        return count($this->petRepository->findByOwner($user));
    }
}
