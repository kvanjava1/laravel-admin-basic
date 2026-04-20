<?php

namespace App\Http\Controllers\Api\AdminDashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDashboard\Article\StoreRequest;
use App\Http\Requests\AdminDashboard\Article\UpdateRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articleService
    ) {
    }

    /**
     * Display a listing of the articles.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->listArticles(
            $request->only(['search', 'category_id', 'status']),
            $request->get('per_page', 10)
        );

        return response()->json($articles);
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $article = $this->articleService->createArticle(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article->load(['tags', 'category', 'featuredImage'])
        ], 201);
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json($article->load(['tags', 'category', 'featuredImage', 'author']));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(UpdateRequest $request, Article $article): JsonResponse
    {
        $updatedArticle = $this->articleService->updateArticle(
            $article,
            $request->validated()
        );

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $updatedArticle->load(['tags', 'category', 'featuredImage'])
        ]);
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Article $article): JsonResponse
    {
        $this->articleService->deleteArticle($article);

        return response()->json([
            'message' => 'Article deleted successfully'
        ]);
    }
}
