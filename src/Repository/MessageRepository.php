<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Message Repository
 * Handles database queries for Message entity
 * 
 * Implementation by: Emna Moussa
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Get conversation between two users with pagination
     */
    public function findConversation(User $user1, User $user2, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->where(
                '(m.sender = :user1 AND m.receiver = :user2) OR '
                . '(m.sender = :user2 AND m.receiver = :user1)'
            )
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get unread message count for a user
     */
    public function countUnreadMessages(User $user): int
    {
        return (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :user AND m.readAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get unread message count from a specific sender
     */
    public function countUnreadFrom(User $receiver, User $sender): int
    {
        return (int)$this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :receiver AND m.sender = :sender AND m.readAt IS NULL')
            ->setParameter('receiver', $receiver)
            ->setParameter('sender', $sender)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(User $receiver, User $sender): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.readAt', ':now')
            ->where('m.receiver = :receiver AND m.sender = :sender AND m.readAt IS NULL')
            ->setParameter('now', new \DateTime())
            ->setParameter('receiver', $receiver)
            ->setParameter('sender', $sender)
            ->getQuery()
            ->execute();
    }

    /**
     * Get last message with user
     */
    public function findLastMessage(User $user1, User $user2): ?Message
    {
        return $this->createQueryBuilder('m')
            ->where(
                '(m.sender = :user1 AND m.receiver = :user2) OR '
                . '(m.sender = :user2 AND m.receiver = :user1)'
            )
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Search messages in conversation
     */
    public function searchInConversation(User $user1, User $user2, string $query, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where(
                '((m.sender = :user1 AND m.receiver = :user2) OR '
                . '(m.sender = :user2 AND m.receiver = :user1))'
            )
            ->andWhere('m.content LIKE :query')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults($limit)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get conversation list for user with last message
     */
    public function findConversationList(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->select('m, CASE WHEN m.sender = :user THEN m.receiver ELSE m.sender END as otherUser')
            ->where('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->groupBy('CASE WHEN m.sender = :user THEN m.receiver ELSE m.sender END')
            ->orderBy('MAX(m.createdAt)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
