<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Repositories\TagRepository;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    public function __construct(
        protected ArticleRepository $articleRepository,
        protected TagRepository $tagRepository
    ) {
    }

    public function listArticles(array $filters = [], int $perPage = 10)
    {
        return $this->articleRepository->paginate($filters, $perPage);
    }

    public function createArticle(array $data, int $authorId): Article
    {
        return DB::transaction(function () use ($data, $authorId) {
            $data['author_id'] = $authorId;
            
            // Auto-set published_at if status is published and not provided
            if (($data['status'] ?? null) === 'published' && !isset($data['published_at'])) {
                $data['published_at'] = now();
            }
            
            $article = $this->articleRepository->create($data);

            if (isset($data['tags'])) {
                $tagIds = $this->tagRepository->findOrCreateByNames($data['tags']);
                $article->tags()->sync($tagIds);
            }

            return $article;
        });
    }

    public function updateArticle(Article $article, array $data): Article
    {
        return DB::transaction(function () use ($article, $data) {
            // Auto-set published_at if status is changed to published and it's currently null
            if (($data['status'] ?? null) === 'published' && !$article->published_at && !isset($data['published_at'])) {
                $data['published_at'] = now();
            }

            $this->articleRepository->update($article, $data);

            if (isset($data['tags'])) {
                $tagIds = $this->tagRepository->findOrCreateByNames($data['tags']);
                $article->tags()->sync($tagIds);
            }

            return $article;
        });
    }

    public function deleteArticle(Article $article): bool
    {
        return $this->articleRepository->delete($article);
    }
}
