<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Chat API Controller
 * Handles messaging, read receipts, and conversation management
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/chat')]
class ChatApiController extends AbstractController
{
    public function __construct(private ChatService $chatService) {}

    /**
     * Send message
     */
    #[Route('/messages', name: 'api_chat_send', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['receiverId'], $data['content'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            // TODO: Get receiver from repository
            // $receiver = $userRepository->find($data['receiverId']);

            // Send message
            // $message = $this->chatService->sendMessage($user, $receiver, $data['content']);

            return $this->json([
                'message' => 'Message sent',
                // 'data' => ['id' => $message->getId(), 'createdAt' => $message->getCreatedAt()?->format('c')]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Send failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get conversation
     */
    #[Route('/conversations/{recipientId}', name: 'api_chat_conversation', methods: ['GET'])]
    public function getConversation(
        Request $request,
        int $recipientId,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // TODO: Get recipient from repository
            // $recipient = $userRepository->find($recipientId);

            $limit = (int)$request->query->get('limit', 50);
            $offset = (int)$request->query->get('offset', 0);

            // Mark messages as read
            // $this->chatService->markAsRead($user, $recipient);

            // Get conversation
            // $messages = $this->chatService->getConversation($user, $recipient, $limit, $offset);

            return $this->json([
                // 'messages' => array_map(fn($msg) => [
                //     'id' => $msg->getId(),
                //     'sender' => ['id' => $msg->getSender()->getId()],
                //     'content' => $msg->getContent(),
                //     'createdAt' => $msg->getCreatedAt()?->format('c'),
                //     'isRead' => $msg->isRead(),
                // ], $messages)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Fetch failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get unread count
     */
    #[Route('/unread', name: 'api_chat_unread', methods: ['GET'])]
    public function getUnreadCount(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $count = $this->chatService->getUnreadCount($user);

        return $this->json(['unread_count' => $count]);
    }

    /**
     * Search conversation
     */
    #[Route('/search', name: 'api_chat_search', methods: ['GET'])]
    public function search(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $query = $request->query->get('q', '');
            $recipientId = (int)$request->query->get('recipientId');

            if (!$query || !$recipientId) {
                return $this->json(['error' => 'Missing parameters'], Response::HTTP_BAD_REQUEST);
            }

            // TODO: Get recipient from repository
            // $recipient = $userRepository->find($recipientId);
            // $results = $this->chatService->searchConversation($user, $recipient, $query, 50);

            return $this->json([
                // 'results' => array_map(fn($msg) => [...], $results)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Search failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
