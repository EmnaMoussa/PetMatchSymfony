<?php

namespace App\Controller;

use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contact API Controller
 * Handles contact form submissions and support
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/contact')]
class ContactApiController extends AbstractController
{
    public function __construct(private ContactService $contactService) {}

    /**
     * Submit contact form
     */
    #[Route('', name: 'api_contact_submit', methods: ['POST'])]
    public function submit(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name'], $data['email'], $data['subject'], $data['message'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json(['error' => 'Invalid email'], Response::HTTP_BAD_REQUEST);
            }

            $submission = $this->contactService->submitContact(
                $data['name'],
                $data['email'],
                $data['subject'],
                $data['message']
            );

            return $this->json([
                'message' => 'Your message has been sent successfully',
                'submission_id' => $submission->getId(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Submission failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
