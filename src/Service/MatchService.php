<?php

namespace App\Service;

use App\Entity\Match;
use App\Repository\MatchRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Match Service
 * Handles match management and match-related operations
 * 
 * Implementation by: Emna Moussa
 */
class MatchService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MatchRepository $matchRepository,
    ) {}

    /**
     * Get user's active matches
     */
    public function getActiveMatches(User $user, int $limit = 50, int $offset = 0): array
    {
        return $this->matchRepository->findActiveMatches($user, $limit, $offset);
    }

    /**
     * Get count of active matches
     */
    public function getActiveMatchCount(User $user): int
    {
        return $this->matchRepository->countActiveMatches($user);
    }

    /**
     * Unmatch with user
     */
    public function unmatch(Match $match): bool
    {
        $match->setIsActive(false);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get recent matches for notifications
     */
    public function getRecentMatches(User $user, int $days = 7): array
    {
        return $this->matchRepository->findRecentMatches($user, $days);
    }

    /**
     * Get matched user from match
     */
    public function getMatchedUser(Match $match, User $currentUser)
    {
        return $this->matchRepository->getMatchedUser($match, $currentUser);
    }
}
