<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Pet;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * User Service
 * Handles user profile management, authentication, and account operations
 * 
 * Implementation by: Emna Moussa
 */
class UserService
{
    private string $uploadDir = 'uploads/profiles';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ImageService $imageService,
    ) {}

    /**
     * Create a new user account
     */
    public function createUser(
        string $email,
        string $firstName,
        string $lastName,
        string $plainPassword,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile(
        User $user,
        string $firstName,
        string $lastName,
        ?string $bio = null,
        ?string $location = null,
        ?\DateTime $dateOfBirth = null,
    ): User {
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setBio($bio);
        $user->setLocation($location);
        $user->setDateOfBirth($dateOfBirth);
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $user;
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(User $user, UploadedFile $file): string
    {
        // Delete old image if exists
        if ($user->getProfileImage()) {
            $this->imageService->deleteImage($user->getProfileImage());
        }

        // Upload new image
        $imagePath = $this->imageService->uploadImage($file, $this->uploadDir);
        $user->setProfileImage($imagePath);
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $imagePath;
    }

    /**
     * Change password
     */
    public function changePassword(User $user, string $plainPassword): User
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $user;
    }

    /**
     * Delete user account (soft delete - anonymize data)
     */
    public function deleteAccount(User $user): bool
    {
        // Delete profile image
        if ($user->getProfileImage()) {
            $this->imageService->deleteImage($user->getProfileImage());
        }

        // Delete pet images
        foreach ($user->getPets() as $pet) {
            if ($pet->getImage()) {
                $this->imageService->deleteImage($pet->getImage());
            }
        }

        // Remove user and cascade delete related data
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get user profile completion percentage
     */
    public function getProfileCompletion(User $user): int
    {
        $completion = 0;
        $total = 7; // Total profile fields

        // Email (25%)
        if ($user->getEmail()) $completion++;
        
        // First name (25%)
        if ($user->getFirstName()) $completion++;
        
        // Last name (25%)
        if ($user->getLastName()) $completion++;
        
        // Bio (15%)
        if ($user->getBio()) $completion++;
        
        // Profile image (15%)
        if ($user->getProfileImage()) $completion++;
        
        // Location (15%)
        if ($user->getLocation()) $completion++;
        
        // At least one pet (15%)
        if (count($user->getPets()) > 0) $completion++;

        return (int)(($completion / $total) * 100);
    }

    /**
     * Get public profile
     */
    public function getPublicProfile(User $user): array
    {
        return [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'bio' => $user->getBio(),
            'profileImage' => $user->getProfileImage(),
            'location' => $user->getLocation(),
            'pets' => array_map(fn(Pet $pet) => [
                'id' => $pet->getId(),
                'name' => $pet->getName(),
                'type' => $pet->getType(),
                'breed' => $pet->getBreed(),
                'age' => $pet->getAge(),
                'gender' => $pet->getGender(),
                'image' => $pet->getImage(),
                'description' => $pet->getDescription(),
            ], $user->getPets()->toArray()),
        ];
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }
}
