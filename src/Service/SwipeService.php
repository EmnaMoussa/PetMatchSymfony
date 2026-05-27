<?php

namespace App\Service;

use App\Entity\Swipe;
use App\Entity\User;
use App\Entity\Match;
use App\Repository\SwipeRepository;
use App\Repository\MatchRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Swipe Service
 * Handles swipe logic, match detection, and statistics
 * 
 * Implementation by: Emna Moussa
 */
class SwipeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SwipeRepository $swipeRepository,
        private MatchRepository $matchRepository,
    ) {}

    /**
     * Process a swipe action
     * Returns true if it's a mutual match
     */
    public function processSwipe(User $userFrom, User $userTo, bool $isLike): bool
    {
        // Check if already swiped
        $existingSwipe = $this->swipeRepository->hasUserSwiped($userFrom, $userTo);
        if ($existingSwipe) {
            return false; // Already swiped
        }

        // Create new swipe
        $swipe = new Swipe();
        $swipe->setUserFrom($userFrom);
        $swipe->setUserTo($userTo);
        $swipe->setIsLike($isLike);

        $this->entityManager->persist($swipe);
        $this->entityManager->flush();

        // Check for mutual match
        if ($isLike) {
            return $this->checkAndCreateMatch($userFrom, $userTo);
        }

        return false;
    }

    /**
     * Check for mutual like and create match if exists
     */
    private function checkAndCreateMatch(User $user1, User $user2): bool
    {
        // Check if user2 already liked user1
        $reverseSwipe = $this->swipeRepository->hasUserSwiped($user2, $user1);
        
        if (!$reverseSwipe || !$reverseSwipe->isLike()) {
            return false; // Not a mutual match yet
        }

        // Check if match already exists
        $existingMatch = $this->matchRepository->findMatchBetween($user1, $user2);
        if ($existingMatch) {
            return true; // Match already exists
        }

        // Create new match
        $match = new Match();
        $match->setUser($user1);
        $match->setMatchedWith($user2);
        $match->setIsActive(true);

        $this->entityManager->persist($match);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Undo last swipe
     */
    public function undoLastSwipe(User $user): bool
    {
        $history = $this->swipeRepository->getUserSwipeHistory($user, 1);
        
        if (empty($history)) {
            return false;
        }

        $lastSwipe = $history[0];
        $this->entityManager->remove($lastSwipe);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get swipe statistics for user
     */
    public function getSwipeStats(User $user): array
    {
        return $this->swipeRepository->getSwipeStats($user);
    }

    /**
     * Get likes received
     */
    public function getLikesReceived(User $user, int $limit = 50): array
    {
        return $this->swipeRepository->getLikesReceived($user, $limit);
    }

    /**
     * Check if two users have matched
     */
    public function hasMutualMatch(User $user1, User $user2): bool
    {
        $match = $this->matchRepository->findMatchBetween($user1, $user2);
        return $match && $match->isActive();
    }
}
