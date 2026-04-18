<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'caption' => $this->caption,
            'alt_text' => $this->alt_text,
            'output_mime_type' => $this->output_mime_type,
            'created_at' => $this->created_at,
            'original_url' => $this->original_url,
            'thumbnail_url' => $this->thumbnail_url,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'slug' => $this->category?->slug,
                ];
            }),
            'tags_preview' => $this->whenLoaded('tags', function () {
                return $this->tags
                    ->take(2)
                    ->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ])
                    ->values();
            }, []),
        ];
    }
}
