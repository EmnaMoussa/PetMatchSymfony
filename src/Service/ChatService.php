<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Chat Service
 * Handles message operations, read receipts, and conversation management
 * 
 * Implementation by: Emna Moussa
 */
class ChatService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageRepository $messageRepository,
    ) {}

    /**
     * Send a message between two users
     */
    public function sendMessage(User $sender, User $receiver, string $content): Message
    {
        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setContent($content);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * Get conversation between two users
     */
    public function getConversation(User $user1, User $user2, int $limit = 50, int $offset = 0): array
    {
        return $this->messageRepository->findConversation($user1, $user2, $limit, $offset);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(User $receiver, User $sender): void
    {
        $this->messageRepository->markAsRead($receiver, $sender);
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messageRepository->countUnreadMessages($user);
    }

    /**
     * Get unread count from specific user
     */
    public function getUnreadCountFrom(User $receiver, User $sender): int
    {
        return $this->messageRepository->countUnreadFrom($receiver, $sender);
    }

    /**
     * Get last message with user
     */
    public function getLastMessage(User $user1, User $user2): ?Message
    {
        return $this->messageRepository->findLastMessage($user1, $user2);
    }

    /**
     * Search messages in conversation
     */
    public function searchConversation(User $user1, User $user2, string $query, int $limit = 50): array
    {
        return $this->messageRepository->searchInConversation($user1, $user2, $query, $limit);
    }

    /**
     * Delete message (soft delete by clearing content)
     */
    public function deleteMessage(Message $message, User $user): bool
    {
        // Only allow sender to delete their own message
        if ($message->getSender()->getId() !== $user->getId()) {
            return false;
        }

        $message->setContent('[Message deleted]');
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get conversation list with last message
     */
    public function getConversationList(User $user, int $limit = 50): array
    {
        return $this->messageRepository->findConversationList($user, $limit);
    }
}
