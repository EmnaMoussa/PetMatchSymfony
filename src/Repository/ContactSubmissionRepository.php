<?php

namespace App\Repository;

use App\Entity\ContactSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ContactSubmission Repository
 * Handles database queries for ContactSubmission entity
 * 
 * Implementation by: Emna Moussa
 */
class ContactSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactSubmission::class);
    }

    /**
     * Find unread submissions
     */
    public function findUnread(int $limit = 50): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isRead = false')
            ->setMaxResults($limit)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unread submissions
     */
    public function countUnread(): int
    {
        return (int)$this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isRead = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find unanswered submissions
     */
    public function findUnanswered(int $limit = 50): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.response IS NULL')
            ->setMaxResults($limit)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by email
     */
    public function findByEmail(string $email, int $limit = 50): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults($limit)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search submissions
     */
    public function search(string $query, int $limit = 50): array
    {
        return $this->createQueryBuilder('c')
            ->where(
                'c.name LIKE :query OR '
                . 'c.email LIKE :query OR '
                . 'c.subject LIKE :query OR '
                . 'c.message LIKE :query'
            )
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults($limit)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
