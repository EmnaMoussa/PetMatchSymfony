<?php

namespace App\Repository;

use App\Entity\Swipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Swipe Repository
 * Handles database queries for Swipe entity
 * 
 * Implementation by: Emna Moussa
 */
class SwipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Swipe::class);
    }

    /**
     * Check if user already swiped on another user
     */
    public function hasUserSwiped(User $userFrom, User $userTo): ?Swipe
    {
        return $this->findOneBy([
            'userFrom' => $userFrom,
            'userTo' => $userTo,
        ]);
    }

    /**
     * Check for mutual swipe (both users like each other)
     */
    public function findMutualSwipe(User $user1, User $user2): ?Swipe
    {
        $swipe1 = $this->createQueryBuilder('s')
            ->where('s.userFrom = :user1 AND s.userTo = :user2 AND s.isLike = true')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$swipe1) {
            return null;
        }

        $swipe2 = $this->createQueryBuilder('s')
            ->where('s.userFrom = :user2 AND s.userTo = :user1 AND s.isLike = true')
            ->setParameter('user2', $user2)
            ->setParameter('user1', $user1)
            ->getQuery()
            ->getOneOrNullResult();

        return $swipe2 ? $swipe1 : null;
    }

    /**
     * Get user's swipe history
     */
    public function getUserSwipeHistory(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.userFrom = :user')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get likes received by user
     */
    public function getLikesReceived(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.userTo = :user AND s.isLike = true')
            ->setParameter('user', $user)
            ->leftJoin('s.userFrom', 'u')
            ->addSelect('u')
            ->setMaxResults($limit)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get user's likes
     */
    public function getUserLikes(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.userFrom = :user AND s.isLike = true')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get swipe statistics for a user
     */
    public function getSwipeStats(User $user): array
    {
        $likes = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.userFrom = :user AND s.isLike = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $passes = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.userFrom = :user AND s.isLike = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $likesReceived = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.userTo = :user AND s.isLike = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'likes_sent' => (int)$likes,
            'passes_sent' => (int)$passes,
            'likes_received' => (int)$likesReceived,
        ];
    }
}
