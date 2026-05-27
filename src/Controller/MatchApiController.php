<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MatchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Match API Controller
 * Handles match list, notifications, and match management
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/matches')]
class MatchApiController extends AbstractController
{
    public function __construct(private MatchService $matchService) {}

    /**
     * Get user's active matches
     */
    #[Route('', name: 'api_match_list', methods: ['GET'])]
    public function getMatches(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $limit = (int)$request->query->get('limit', 50);
            $offset = (int)$request->query->get('offset', 0);

            $matches = $this->matchService->getActiveMatches($user, $limit, $offset);
            $count = $this->matchService->getActiveMatchCount($user);

            return $this->json([
                'total' => $count,
                'matches' => array_map(fn($match) => [
                    'id' => $match->getId(),
                    'matchedWith' => [
                        'id' => $match->getMatchedWith()->getId(),
                        'firstName' => $match->getMatchedWith()->getFirstName(),
                        'lastName' => $match->getMatchedWith()->getLastName(),
                        'profileImage' => $match->getMatchedWith()->getProfileImage(),
                    ],
                    'createdAt' => $match->getCreatedAt()?->format('c'),
                ], $matches)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Fetch failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get match count
     */
    #[Route('/count', name: 'api_match_count', methods: ['GET'])]
    public function getMatchCount(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $count = $this->matchService->getActiveMatchCount($user);

        return $this->json(['count' => $count]);
    }

    /**
     * Unmatch
     */
    #[Route('/{matchId}', name: 'api_match_delete', methods: ['DELETE'])]
    public function unmatch(
        int $matchId,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // TODO: Get match from repository and verify ownership
            // $match = $matchRepository->find($matchId);
            // $this->matchService->unmatch($match);

            return $this->json(['message' => 'Unmatched successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Unmatch failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get recent matches
     */
    #[Route('/recent', name: 'api_match_recent', methods: ['GET'])]
    public function getRecent(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $days = (int)$request->query->get('days', 7);
        $matches = $this->matchService->getRecentMatches($user, $days);

        return $this->json([
            'total' => count($matches),
            'matches' => array_map(fn($match) => [
                'id' => $match->getId(),
                'matchedWith' => [
                    'id' => $match->getMatchedWith()->getId(),
                    'firstName' => $match->getMatchedWith()->getFirstName(),
                    'lastName' => $match->getMatchedWith()->getLastName(),
                ],
                'createdAt' => $match->getCreatedAt()?->format('c'),
            ], $matches)
        ]);
    }
}
