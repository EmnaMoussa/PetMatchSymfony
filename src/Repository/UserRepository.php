<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * User Repository
 * Handles database queries for User entity
 * 
 * Implementation by: Emna Moussa
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find users by location
     */
    public function findByLocation(string $location, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.location = :location')
            ->setParameter('location', $location)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get paginated users excluding given user
     */
    public function findCandidatesForUser(User $currentUser, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id != :userId')
            ->setParameter('userId', $currentUser->getId())
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Upgrade password in database
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Get users by pet type preference
     */
    public function findByPetType(string $petType, int $limit = 20): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.pets', 'p')
            ->where('p.type = :type')
            ->setParameter('type', $petType)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users with at least one pet
     */
    public function findWithPets(int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.pets', 'p')
            ->where('p.id IS NOT NULL')
            ->distinct(true)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
