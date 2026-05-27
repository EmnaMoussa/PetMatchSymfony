<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\PetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * User API Controller
 * Handles user authentication, profile management
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/users')]
class UserApiController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private PetService $petService,
    ) {}

    /**
     * Register new user
     */
    #[Route('/register', name: 'api_user_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'], $data['firstName'], $data['lastName'], $data['password'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            // Check if user exists
            if ($this->userService->findByEmail($data['email'])) {
                return $this->json(['error' => 'Email already registered'], Response::HTTP_CONFLICT);
            }

            $user = $this->userService->createUser(
                $data['email'],
                $data['firstName'],
                $data['lastName'],
                $data['password']
            );

            return $this->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Registration failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get current user profile
     */
    #[Route('/me', name: 'api_user_profile', methods: ['GET'])]
    public function getProfile(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->userService->getPublicProfile($user));
    }

    /**
     * Update user profile
     */
    #[Route('/me', name: 'api_user_update', methods: ['PUT'])]
    public function updateProfile(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            $dateOfBirth = null;
            if (isset($data['dateOfBirth'])) {
                $dateOfBirth = new \DateTime($data['dateOfBirth']);
            }

            $this->userService->updateProfile(
                $user,
                $data['firstName'] ?? $user->getFirstName(),
                $data['lastName'] ?? $user->getLastName(),
                $data['bio'] ?? $user->getBio(),
                $data['location'] ?? $user->getLocation(),
                $dateOfBirth
            );

            return $this->json([
                'message' => 'Profile updated successfully',
                'user' => $this->userService->getPublicProfile($user)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Update failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get profile completion percentage
     */
    #[Route('/me/completion', name: 'api_user_completion', methods: ['GET'])]
    public function getCompletion(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'completion' => $this->userService->getProfileCompletion($user)
        ]);
    }

    /**
     * Upload profile image
     */
    #[Route('/me/avatar', name: 'api_user_avatar', methods: ['POST'])]
    public function uploadAvatar(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $file = $request->files->get('avatar');
            if (!$file) {
                return $this->json(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
            }

            $imagePath = $this->userService->uploadProfileImage($user, $file);

            return $this->json([
                'message' => 'Avatar uploaded successfully',
                'image' => $imagePath
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Upload failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change password
     */
    #[Route('/me/password', name: 'api_user_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['newPassword'])) {
                return $this->json(['error' => 'Password required'], Response::HTTP_BAD_REQUEST);
            }

            $this->userService->changePassword($user, $data['newPassword']);

            return $this->json(['message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Change failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user by ID (public profile)
     */
    #[Route('/{id}', name: 'api_user_get', methods: ['GET'])]
    public function showUser(User $user): JsonResponse
    {
        return $this->json($this->userService->getPublicProfile($user));
    }
}
