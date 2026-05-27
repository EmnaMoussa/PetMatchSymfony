<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Pet Repository
 * Handles database queries for Pet entity
 * 
 * Implementation by: Emna Moussa
 */
class PetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pet::class);
    }

    /**
     * Find pets by owner
     */
    public function findByOwner(User $owner): array
    {
        return $this->findBy(['owner' => $owner], ['createdAt' => 'DESC']);
    }

    /**
     * Find pets by type
     */
    public function findByType(string $type, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pets with filters (species, breed, age, location)
     */
    public function findWithFilters(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.owner', 'o')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['type'])) {
            $qb->andWhere('p.type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['breed'])) {
            $qb->andWhere('p.breed = :breed')
                ->setParameter('breed', $filters['breed']);
        }

        if (!empty($filters['minAge'])) {
            $qb->andWhere('p.age >= :minAge')
                ->setParameter('minAge', (int)$filters['minAge']);
        }

        if (!empty($filters['maxAge'])) {
            $qb->andWhere('p.age <= :maxAge')
                ->setParameter('maxAge', (int)$filters['maxAge']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('o.location = :location')
                ->setParameter('location', $filters['location']);
        }

        if (!empty($filters['gender'])) {
            $qb->andWhere('p.gender = :gender')
                ->setParameter('gender', $filters['gender']);
        }

        return $qb->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pets excluding already swiped ones
     */
    public function findAvailableForSwipe(User $currentUser, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.owner != :owner')
            ->setParameter('owner', $currentUser)
            ->leftJoin('p.owner', 'o')
            ->addSelect('o');

        // Exclude already swiped pets
        $subQuery = $this->createQueryBuilder('p2')
            ->select('IDENTITY(s.userTo)')
            ->from('App\\Entity\\Swipe', 's')
            ->where('s.userFrom = :currentUser')
            ->getDQL();

        $qb->andWhere('p.id NOT IN (' . $subQuery . ')')
            ->setParameter('currentUser', $currentUser);

        return $qb->setMaxResults($limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get pet by ID with owner details
     */
    public function findWithOwner(int $petId): ?Pet
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.owner', 'o')
            ->addSelect('o')
            ->where('p.id = :id')
            ->setParameter('id', $petId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
