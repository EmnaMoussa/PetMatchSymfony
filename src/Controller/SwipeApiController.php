<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SwipeService;
use App\Repository\PetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Swipe API Controller
 * Handles swipe actions, match detection, and statistics
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/swipes')]
class SwipeApiController extends AbstractController
{
    public function __construct(
        private SwipeService $swipeService,
        private PetRepository $petRepository,
    ) {}

    /**
     * Process a swipe
     */
    #[Route('', name: 'api_swipe_create', methods: ['POST'])]
    public function swipe(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['targetUserId'], $data['isLike'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            // Get target user
            // TODO: Get user from repository
            // $targetUser = $userRepository->find($data['targetUserId']);

            // Process swipe
            // $isMatch = $this->swipeService->processSwipe($user, $targetUser, (bool)$data['isLike']);

            return $this->json([
                'message' => 'Swipe processed',
                // 'matched' => $isMatch
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Swipe failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get swipe statistics
     */
    #[Route('/stats', name: 'api_swipe_stats', methods: ['GET'])]
    public function getStats(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $stats = $this->swipeService->getSwipeStats($user);

        return $this->json($stats);
    }

    /**
     * Get likes received
     */
    #[Route('/likes-received', name: 'api_swipe_likes_received', methods: ['GET'])]
    public function getLikesReceived(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $limit = (int)$request->query->get('limit', 50);
        $likes = $this->swipeService->getLikesReceived($user, $limit);

        return $this->json([
            'total' => count($likes),
            'likes' => array_map(fn($like) => [
                'id' => $like->getId(),
                'user' => [
                    'id' => $like->getUserFrom()->getId(),
                    'firstName' => $like->getUserFrom()->getFirstName(),
                    'lastName' => $like->getUserFrom()->getLastName(),
                    'profileImage' => $like->getUserFrom()->getProfileImage(),
                ],
                'createdAt' => $like->getCreatedAt()?->format('c'),
            ], $likes)
        ]);
    }

    /**
     * Undo last swipe
     */
    #[Route('/undo', name: 'api_swipe_undo', methods: ['POST'])]
    public function undoSwipe(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $success = $this->swipeService->undoLastSwipe($user);

            if (!$success) {
                return $this->json(['error' => 'No swipe to undo'], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['message' => 'Swipe undone']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Undo failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
