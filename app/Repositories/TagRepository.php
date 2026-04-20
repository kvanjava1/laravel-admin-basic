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
    /**
     * Find or create tags by their names.
     */
    public function findOrCreateByNames(array $names): array
    {
        $tagIds = [];
        foreach ($names as $name) {
            $tag = Tag::firstOrCreate(
                ['name' => $name],
                ['slug' => \Illuminate\Support\Str::slug($name)]
            );
            $tagIds[] = $tag->id;
        }
        return $tagIds;
    }
}
