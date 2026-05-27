<?php

namespace App\Service;

use App\Entity\ContactSubmission;
use App\Repository\ContactSubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Contact Service
 * Handles contact form submissions and support tickets
 * 
 * Implementation by: Emna Moussa
 */
class ContactService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContactSubmissionRepository $contactRepository,
        private MailerInterface $mailer,
        private string $adminEmail = 'support@petmatch.com',
    ) {}

    /**
     * Submit contact form
     */
    public function submitContact(
        string $name,
        string $email,
        string $subject,
        string $message,
    ): ContactSubmission {
        $submission = new ContactSubmission();
        $submission->setName($name);
        $submission->setEmail($email);
        $submission->setSubject($subject);
        $submission->setMessage($message);

        $this->entityManager->persist($submission);
        $this->entityManager->flush();

        // Send notification email to admin
        $this->notifyAdmin($submission);

        return $submission;
    }

    /**
     * Send notification email to admin
     */
    private function notifyAdmin(ContactSubmission $submission): void
    {
        try {
            $email = (new Email())
                ->from('noreply@petmatch.com')
                ->to($this->adminEmail)
                ->subject('New Contact Submission: ' . $submission->getSubject())
                ->html($this->renderNotificationTemplate($submission));

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail
        }
    }

    /**
     * Render notification template
     */
    private function renderNotificationTemplate(ContactSubmission $submission): string
    {
        return sprintf(
            '<h2>New Contact Submission</h2>' .
            '<p><strong>Name:</strong> %s</p>' .
            '<p><strong>Email:</strong> %s</p>' .
            '<p><strong>Subject:</strong> %s</p>' .
            '<p><strong>Message:</strong></p>' .
            '<p>%s</p>' .
            '<p><a href="/admin/contact/%d">View Submission</a></p>',
            htmlspecialchars($submission->getName()),
            htmlspecialchars($submission->getEmail()),
            htmlspecialchars($submission->getSubject()),
            nl2br(htmlspecialchars($submission->getMessage())),
            $submission->getId()
        );
    }

    /**
     * Mark submission as read
     */
    public function markAsRead(ContactSubmission $submission): void
    {
        $submission->setIsRead(true);
        $this->entityManager->flush();
    }

    /**
     * Reply to contact submission
     */
    public function replyToContact(ContactSubmission $submission, string $response): ContactSubmission
    {
        $submission->setResponse($response);
        $submission->setRespondedAt(new \DateTime());
        $this->entityManager->flush();

        // Send reply email to user
        $this->sendReplyEmail($submission);

        return $submission;
    }

    /**
     * Send reply email to user
     */
    private function sendReplyEmail(ContactSubmission $submission): void
    {
        try {
            $email = (new Email())
                ->from('support@petmatch.com')
                ->to($submission->getEmail())
                ->subject('Re: ' . $submission->getSubject())
                ->html($this->renderReplyTemplate($submission));

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail
        }
    }

    /**
     * Render reply template
     */
    private function renderReplyTemplate(ContactSubmission $submission): string
    {
        return sprintf(
            '<h2>Thank you for contacting PetMatch</h2>' .
            '<p>Hi %s,</p>' .
            '<p>Here is our response to your inquiry:</p>' .
            '<p>%s</p>' .
            '<p>Best regards,<br>The PetMatch Team</p>',
            htmlspecialchars($submission->getName()),
            nl2br(htmlspecialchars($submission->getResponse()))
        );
    }

    /**
     * Get unread submissions
     */
    public function getUnreadSubmissions(int $limit = 50): array
    {
        return $this->contactRepository->findUnread($limit);
    }

    /**
     * Count unread submissions
     */
    public function countUnread(): int
    {
        return $this->contactRepository->countUnread();
    }

    /**
     * Search submissions
     */
    public function search(string $query, int $limit = 50): array
    {
        return $this->contactRepository->search($query, $limit);
    }
}
