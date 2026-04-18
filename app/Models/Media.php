<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'category_id',
        'created_by',
        'title',
        'slug',
        'alt_text',
        'caption',
        'description',
        'original_path',
        'ratio_16_9_medium_path',
        'ratio_16_9_big_path',
        'ratio_4_3_medium_path',
        'ratio_4_3_big_path',
        'original_mime_type',
        'output_mime_type',
        'original_size',
        'original_output_size',
        'ratio_16_9_medium_size',
        'ratio_16_9_big_size',
        'ratio_4_3_medium_size',
        'ratio_4_3_big_size',
        'file_cleanup_marked_at',
        'crop_16_9_x',
        'crop_16_9_y',
        'crop_16_9_width',
        'crop_16_9_height',
        'crop_4_3_x',
        'crop_4_3_y',
        'crop_4_3_width',
        'crop_4_3_height',
    ];

    protected $appends = [
        'original_url',
        'ratio_16_9_medium_url',
        'ratio_16_9_big_url',
        'ratio_4_3_medium_url',
        'ratio_4_3_big_url',
        'thumbnail_url',
        'variants',
        'total_variant_size',
    ];

    protected $casts = [
        'file_cleanup_marked_at' => 'datetime',
    ];

    /**
     * Get the category associated with this media item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created this media item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    public function getOriginalUrlAttribute(): ?string
    {
        return $this->buildPublicUrl($this->original_path);
    }

    public function getRatio169MediumUrlAttribute(): ?string
    {
        return $this->buildPublicUrl($this->ratio_16_9_medium_path);
    }

    public function getRatio169BigUrlAttribute(): ?string
    {
        return $this->buildPublicUrl($this->ratio_16_9_big_path);
    }

    public function getRatio43MediumUrlAttribute(): ?string
    {
        return $this->buildPublicUrl($this->ratio_4_3_medium_path);
    }

    public function getRatio43BigUrlAttribute(): ?string
    {
        return $this->buildPublicUrl($this->ratio_4_3_big_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->ratio_4_3_medium_url;
    }

    public function getVariantsAttribute(): array
    {
        return [
            $this->buildVariantPayload('Original', 'original', $this->original_path, $this->original_output_size),
            $this->buildVariantPayload('16:9 Medium', 'ratio_16_9_medium', $this->ratio_16_9_medium_path, $this->ratio_16_9_medium_size),
            $this->buildVariantPayload('16:9 Big', 'ratio_16_9_big', $this->ratio_16_9_big_path, $this->ratio_16_9_big_size),
            $this->buildVariantPayload('4:3 Medium', 'ratio_4_3_medium', $this->ratio_4_3_medium_path, $this->ratio_4_3_medium_size),
            $this->buildVariantPayload('4:3 Big', 'ratio_4_3_big', $this->ratio_4_3_big_path, $this->ratio_4_3_big_size),
        ];
    }

    public function getTotalVariantSizeAttribute(): int
    {
        return collect($this->variants)->sum(fn (array $variant) => $variant['size'] ?? 0);
    }

    protected function buildPublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk(config('media.disk', 'public'))->url($path);
    }

    protected function buildVariantPayload(string $label, string $key, ?string $path, ?int $size): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'path' => $path,
            'url' => $this->buildPublicUrl($path),
            'size' => max(0, (int) $size),
        ];
    }
}
