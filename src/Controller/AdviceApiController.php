<?php

namespace App\Controller;

use App\Service\AdviceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Advice API Controller
 * Handles pet care tips and advice articles
 * 
 * Implementation by: Emna Moussa
 */
#[Route('/api/advice')]
class AdviceApiController extends AbstractController
{
    public function __construct(private AdviceService $adviceService) {}

    /**
     * Get published articles
     */
    #[Route('', name: 'api_advice_list', methods: ['GET'])]
    public function getArticles(Request $request): JsonResponse
    {
        try {
            $limit = (int)$request->query->get('limit', 20);
            $offset = (int)$request->query->get('offset', 0);

            $articles = $this->adviceService->getPublishedArticles($limit, $offset);

            return $this->json([
                'total' => count($articles),
                'articles' => array_map(fn($article) => [
                    'id' => $article->getId(),
                    'title' => $article->getTitle(),
                    'excerpt' => $article->getExcerpt(),
                    'category' => $article->getCategory(),
                    'image' => $article->getImage(),
                    'publishedAt' => $article->getPublishedAt()?->format('c'),
                ], $articles)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Fetch failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get article by ID
     */
    #[Route('/{articleId}', name: 'api_advice_show', methods: ['GET'])]
    public function getArticle(int $articleId): JsonResponse
    {
        try {
            // TODO: Get article from repository
            // $article = $adviceRepository->find($articleId);

            return $this->json([
                // 'id' => $article->getId(),
                // 'title' => $article->getTitle(),
                // 'content' => $article->getContent(),
                // 'category' => $article->getCategory(),
                // 'image' => $article->getImage(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Search articles
     */
    #[Route('/search', name: 'api_advice_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('q', '');

            if (!$query) {
                return $this->json(['error' => 'Query required'], Response::HTTP_BAD_REQUEST);
            }

            $articles = $this->adviceService->search($query, 20);

            return $this->json([
                'total' => count($articles),
                'articles' => array_map(fn($article) => [
                    'id' => $article->getId(),
                    'title' => $article->getTitle(),
                    'excerpt' => $article->getExcerpt(),
                    'category' => $article->getCategory(),
                ], $articles)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Search failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get categories
     */
    #[Route('/categories', name: 'api_advice_categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->adviceService->getCategories();

            return $this->json([
                'categories' => array_map(fn($cat) => [
                    'name' => $cat['category'],
                    'count' => $cat['count'],
                ], $categories)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Fetch failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
