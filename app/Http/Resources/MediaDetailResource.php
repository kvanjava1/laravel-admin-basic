<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaDetailResource extends JsonResource
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
            'uuid' => $this->uuid,
            'category_id' => $this->category_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'slug' => $this->slug,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'description' => $this->description,
            'original_path' => $this->original_path,
            'ratio_16_9_medium_path' => $this->ratio_16_9_medium_path,
            'ratio_16_9_big_path' => $this->ratio_16_9_big_path,
            'ratio_4_3_medium_path' => $this->ratio_4_3_medium_path,
            'ratio_4_3_big_path' => $this->ratio_4_3_big_path,
            'original_mime_type' => $this->original_mime_type,
            'output_mime_type' => $this->output_mime_type,
            'original_size' => $this->original_size,
            'original_output_size' => $this->original_output_size,
            'ratio_16_9_medium_size' => $this->ratio_16_9_medium_size,
            'ratio_16_9_big_size' => $this->ratio_16_9_big_size,
            'ratio_4_3_medium_size' => $this->ratio_4_3_medium_size,
            'ratio_4_3_big_size' => $this->ratio_4_3_big_size,
            'crop_16_9_x' => $this->crop_16_9_x,
            'crop_16_9_y' => $this->crop_16_9_y,
            'crop_16_9_width' => $this->crop_16_9_width,
            'crop_16_9_height' => $this->crop_16_9_height,
            'crop_4_3_x' => $this->crop_4_3_x,
            'crop_4_3_y' => $this->crop_4_3_y,
            'crop_4_3_width' => $this->crop_4_3_width,
            'crop_4_3_height' => $this->crop_4_3_height,
            'original_url' => $this->original_url,
            'ratio_16_9_medium_url' => $this->ratio_16_9_medium_url,
            'ratio_16_9_big_url' => $this->ratio_16_9_big_url,
            'ratio_4_3_medium_url' => $this->ratio_4_3_medium_url,
            'ratio_4_3_big_url' => $this->ratio_4_3_big_url,
            'thumbnail_url' => $this->thumbnail_url,
            'variants' => $this->variants,
            'total_variant_size' => $this->total_variant_size,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'slug' => $this->category?->slug,
                ];
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->values();
            }, []),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator?->id,
                    'name' => $this->creator?->name,
                    'email' => $this->creator?->email,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
