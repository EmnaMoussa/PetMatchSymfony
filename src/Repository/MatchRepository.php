<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Match Repository
 * Handles database queries for Match entity
 * 
 * Implementation by: Emna Moussa
 */
class MatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMatch::class);
    }

    /**
     * Get all active matches for a user
     */
    public function findActiveMatches(User $user, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.user = :user OR m.matchedWith = :user) AND m.isActive = true')
            ->setParameter('user', $user)
            ->leftJoin('m.matchedWith', 'matched')
            ->addSelect('matched')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get count of active matches for user
     */
    public function countActiveMatches(User $user): int
    {
        return (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('(m.user = :user OR m.matchedWith = :user) AND m.isActive = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Check if match exists between two users
     */
    public function findMatchBetween(User $user1, User $user2): ?UserMatch
    {
        return $this->createQueryBuilder('m')
            ->where(
                '(m.user = :user1 AND m.matchedWith = :user2) OR '
                . '(m.user = :user2 AND m.matchedWith = :user1)'
            )
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the other user in a match
     */
    public function getMatchedUser(UserMatch $match, User $currentUser): ?User
    {
        if ($match->getUser()->getId() === $currentUser->getId()) {
            return $match->getMatchedWith();
        }
        return $match->getUser();
    }

    /**
     * Get recent matches (for notifications)
     */
    public function findRecentMatches(User $user, int $days = 7): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('m')
            ->where('(m.user = :user OR m.matchedWith = :user) AND m.isActive = true')
            ->andWhere('m.createdAt >= :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
