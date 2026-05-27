<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManager;

/**
 * Image Service
 * Handles image uploads, resizing, and management
 * 
 * Implementation by: Emna Moussa
 */
class ImageService
{
    private string $uploadPath = 'public/uploads';
    private array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxFileSize = 5242880; // 5MB

    public function __construct(private string $projectDir = '') {}

    /**
     * Upload and process image
     */
    public function uploadImage(UploadedFile $file, string $subdir = 'general'): string
    {
        // Validate file
        if (!$this->validateImage($file)) {
            throw new \InvalidArgumentException('Invalid image file');
        }

        // Create directory if not exists
        $uploadDir = $this->projectDir . '/' . $this->uploadPath . '/' . $subdir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $filepath = $uploadDir . '/' . $filename;

        // Move uploaded file
        $file->move($uploadDir, $filename);

        // Optimize image
        $this->optimizeImage($filepath);

        // Return relative path
        return '/uploads/' . $subdir . '/' . $filename;
    }

    /**
     * Validate image file
     */
    private function validateImage(UploadedFile $file): bool
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            return false;
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = $file->guessExtension();
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-z0-9_-]/i', '', $name);

        return $name . '_' . uniqid() . '.' . $ext;
    }

    /**
     * Optimize image (resize and compress)
     */
    private function optimizeImage(string $filepath): void
    {
        try {
            $manager = new ImageManager();
            $image = $manager->make($filepath);

            // Resize if too large (max width 2000px)
            if ($image->width() > 2000) {
                $image->resize(2000, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save optimized image
            $image->save($filepath, 85); // 85% quality
        } catch (\Exception $e) {
            // If optimization fails, keep original
        }
    }

    /**
     * Delete image
     */
    public function deleteImage(string $imagePath): bool
    {
        $fullPath = $this->projectDir . '/public' . $imagePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return true;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $imagePath): ?array
    {
        $fullPath = $this->projectDir . '/public' . $imagePath;

        if (!file_exists($fullPath)) {
            return null;
        }

        $size = getimagesize($fullPath);

        return $size ? [
            'width' => $size[0],
            'height' => $size[1],
        ] : null;
    }
}
