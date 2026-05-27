<?php

namespace App\Service;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Advice Service
 * Handles pet care advice articles and tips management
 * 
 * Implementation by: Emna Moussa
 */
class AdviceService
{
    private string $uploadDir = 'uploads/advice';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdviceRepository $adviceRepository,
        private ImageService $imageService,
    ) {}

    /**
     * Create advice article
     */
    public function createAdvice(
        string $title,
        string $content,
        ?string $excerpt = null,
        ?string $category = null,
    ): Advice {
        $advice = new Advice();
        $advice->setTitle($title);
        $advice->setContent($content);
        $advice->setExcerpt($excerpt);
        $advice->setCategory($category);
        $advice->setIsPublished(false);

        $this->entityManager->persist($advice);
        $this->entityManager->flush();

        return $advice;
    }

    /**
     * Update advice article
     */
    public function updateAdvice(
        Advice $advice,
        string $title,
        string $content,
        ?string $excerpt = null,
        ?string $category = null,
    ): Advice {
        $advice->setTitle($title);
        $advice->setContent($content);
        $advice->setExcerpt($excerpt);
        $advice->setCategory($category);
        $advice->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $advice;
    }

    /**
     * Publish advice article
     */
    public function publishAdvice(Advice $advice): Advice
    {
        $advice->setIsPublished(true);
        $advice->setPublishedAt(new \DateTime());
        $advice->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $advice;
    }

    /**
     * Unpublish advice article
     */
    public function unpublishAdvice(Advice $advice): Advice
    {
        $advice->setIsPublished(false);
        $advice->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $advice;
    }

    /**
     * Upload article image
     */
    public function uploadImage(Advice $advice, UploadedFile $file): string
    {
        // Delete old image if exists
        if ($advice->getImage()) {
            $this->imageService->deleteImage($advice->getImage());
        }

        // Upload new image
        $imagePath = $this->imageService->uploadImage($file, $this->uploadDir);
        $advice->setImage($imagePath);
        $advice->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $imagePath;
    }

    /**
     * Delete advice article
     */
    public function deleteAdvice(Advice $advice): bool
    {
        if ($advice->getImage()) {
            $this->imageService->deleteImage($advice->getImage());
        }

        $this->entityManager->remove($advice);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get published articles
     */
    public function getPublishedArticles(int $limit = 20, int $offset = 0): array
    {
        return $this->adviceRepository->findPublished($limit, $offset);
    }

    /**
     * Get articles by category
     */
    public function getByCategory(string $category, int $limit = 20, int $offset = 0): array
    {
        return $this->adviceRepository->findByCategory($category, $limit, $offset);
    }

    /**
     * Search articles
     */
    public function search(string $query, int $limit = 20): array
    {
        return $this->adviceRepository->search($query, $limit);
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return $this->adviceRepository->getCategories();
    }
}
