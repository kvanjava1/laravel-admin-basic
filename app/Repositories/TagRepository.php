<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Support\Collection;

class TagRepository
{
    /**
     * Get tag options for autocomplete.
     */
    public function getOptions(?string $search = null, int $limit = 50): Collection
    {
        return Tag::query()
            ->select(['id', 'name', 'slug'])
            ->when($search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
