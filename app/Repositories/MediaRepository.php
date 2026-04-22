<?php

namespace App\Repositories;

use App\Models\Media;

class MediaRepository
{
    /**
     * Create a new media record.
     */
    public function create(array $data): Media
    {
        return Media::create($data);
    }

    public function update(Media $media, array $data): bool
    {
        return $media->update($data);
    }

    public function delete(Media $media): bool
    {
        return (bool) $media->delete();
    }

    /**
     * Find paginated media records.
     */
    public function paginate(int $perPage = 12, array $filters = [])
    {
        $query = Media::query()
            ->with([
                'category:id,name,slug',
                'tags:id,name,slug',
                'creator:id,name,email',
            ])
            ->latest();

        foreach (['title', 'alt_text', 'caption', 'description'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, 'like', '%' . $filters[$field] . '%');
            }
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $tags = collect($filters['tags'])
                ->filter(fn ($tag) => filled($tag))
                ->values();

            if ($tags->isNotEmpty()) {
                $query->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->where(function ($nestedQuery) use ($tags) {
                        foreach ($tags as $tag) {
                            $nestedQuery->orWhere('name', 'like', '%' . $tag . '%')
                                ->orWhere('slug', 'like', '%' . $tag . '%');
                        }
                    });
                });
            }
        }

        return $query->paginate($perPage);
    }
}
