<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Advice Repository
 * Handles database queries for Advice entity
 * 
 * Implementation by: Emna Moussa
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    /**
     * Find published advice articles
     */
    public function findPublished(int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = true')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find advice by category
     */
    public function findByCategory(string $category, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = true')
            ->andWhere('a.category = :category')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('category', $category)
            ->setParameter('now', new \DateTime())
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search advice articles
     */
    public function search(string $query, int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = true')
            ->andWhere('a.publishedAt <= :now')
            ->andWhere(
                'a.title LIKE :query OR '
                . 'a.excerpt LIKE :query OR '
                . 'a.content LIKE :query'
            )
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($limit)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all categories with count
     */
    public function getCategories(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.category, COUNT(a.id) as count')
            ->where('a.isPublished = true')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('now', new \DateTime())
            ->groupBy('a.category')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recent advice
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = true')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($limit)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
