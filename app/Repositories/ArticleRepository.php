<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Article::query()
            ->with(['author', 'category', 'featuredImage'])
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%');
            })
            ->when(isset($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): Article
    {
        return Article::with(['tags', 'featuredImage', 'category', 'author'])->findOrFail($id);
    }

    public function create(array $data): Article
    {
        return Article::create($data);
    }

    public function update(Article $article, array $data): bool
    {
        return $article->update($data);
    }

    public function delete(Article $article): bool
    {
        return $article->delete();
    }
}
