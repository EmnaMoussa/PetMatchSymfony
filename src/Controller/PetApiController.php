<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Pet API Controller
 * Handles pet profile management and filtering
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/pets')]
class PetApiController extends AbstractController
{
    public function __construct(private PetService $petService) {}

    /**
     * Create new pet
     */
    #[Route('', name: 'api_pet_create', methods: ['POST'])]
    public function create(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name'], $data['type'], $data['breed'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            $pet = $this->petService->createPet(
                $user,
                $data['name'],
                $data['type'],
                $data['breed'],
                $data['age'] ?? null,
                $data['gender'] ?? null,
                $data['description'] ?? null
            );

            return $this->json([
                'message' => 'Pet created successfully',
                'pet' => [
                    'id' => $pet->getId(),
                    'name' => $pet->getName(),
                    'type' => $pet->getType(),
                    'breed' => $pet->getBreed(),
                    'age' => $pet->getAge(),
                    'gender' => $pet->getGender(),
                    'description' => $pet->getDescription(),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Creation failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's pets
     */
    #[Route('/my-pets', name: 'api_pet_my_pets', methods: ['GET'])]
    public function getMyPets(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $pets = $this->petService->getUserPets($user);

        return $this->json([
            'pets' => array_map(fn($pet) => [
                'id' => $pet->getId(),
                'name' => $pet->getName(),
                'type' => $pet->getType(),
                'breed' => $pet->getBreed(),
                'age' => $pet->getAge(),
                'gender' => $pet->getGender(),
                'description' => $pet->getDescription(),
                'image' => $pet->getImage(),
            ], $pets)
        ]);
    }

    /**
     * Get available pets for swipe
     */
    #[Route('/available', name: 'api_pet_available', methods: ['GET'])]
    public function getAvailable(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $limit = (int)$request->query->get('limit', 20);
            $filters = $request->query->all();
            unset($filters['limit']);

            $pets = $this->petService->findWithFilters($filters, $limit);

            return $this->json([
                'total' => count($pets),
                'pets' => array_map(fn($pet) => [
                    'id' => $pet->getId(),
                    'name' => $pet->getName(),
                    'type' => $pet->getType(),
                    'breed' => $pet->getBreed(),
                    'age' => $pet->getAge(),
                    'gender' => $pet->getGender(),
                    'description' => $pet->getDescription(),
                    'image' => $pet->getImage(),
                    'owner' => [
                        'id' => $pet->getOwner()->getId(),
                        'firstName' => $pet->getOwner()->getFirstName(),
                        'lastName' => $pet->getOwner()->getLastName(),
                        'location' => $pet->getOwner()->getLocation(),
                    ]
                ], $pets)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Fetch failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload pet image
     */
    #[Route('/{petId}/image', name: 'api_pet_image', methods: ['POST'])]
    public function uploadImage(
        Request $request,
        int $petId,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // TODO: Get pet from database and verify ownership
            $file = $request->files->get('image');
            if (!$file) {
                return $this->json(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
            }

            // $imagePath = $this->petService->uploadPetImage($pet, $file);

            return $this->json([
                'message' => 'Image uploaded successfully',
                // 'image' => $imagePath
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Upload failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
