<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Tag;
use App\Repositories\MediaRepository;
use App\Exceptions\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class MediaService
{
    public function __construct(
        protected MediaRepository $mediaRepository
    ) {
    }

    /**
     * Get paginated media items.
     */
    public function getPaginatedMedia(int $perPage = 12, array $filters = [])
    {
        return $this->mediaRepository->paginate($perPage, $filters);
    }

    /**
     * Create a media item and its derived assets.
     */
    public function create(array $data): Media
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $data['image'];
        $uuid = (string) Str::uuid();
        $baseDirectory = $this->buildBaseDirectory($uuid);
        $disk = Storage::disk($this->mediaDisk());
        $manager = $this->makeImageManager();
        $quality = $this->mediaQuality();

        try {
            $originalImage = $manager->read($uploadedFile->getRealPath());
            $originalEncoded = (string) $originalImage->toWebp($quality);
            $originalPath = "{$baseDirectory}/original.webp";

            $disk->put($originalPath, $originalEncoded);

            $pathMap = [
                'original_path' => $originalPath,
                'original_output_size' => strlen($originalEncoded),
                ...$this->storeVariant($manager, $uploadedFile, $data, '16x9', 'medium', $baseDirectory),
                ...$this->storeVariant($manager, $uploadedFile, $data, '16x9', 'big', $baseDirectory),
                ...$this->storeVariant($manager, $uploadedFile, $data, '4x3', 'medium', $baseDirectory),
                ...$this->storeVariant($manager, $uploadedFile, $data, '4x3', 'big', $baseDirectory),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            throw new ApiException('Failed to process the uploaded image.', 422);
        }

        $slug = $this->makeUniqueSlug($data['title']);

        $media = $this->mediaRepository->create([
            'uuid' => $uuid,
            'category_id' => $data['category_id'] ?? null,
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'slug' => $slug,
            'alt_text' => $data['alt_text'],
            'caption' => $data['caption'] ?? null,
            'description' => $data['description'] ?? null,
            'original_mime_type' => $uploadedFile->getMimeType(),
            'output_mime_type' => 'image/webp',
            'original_size' => $uploadedFile->getSize(),
            'crop_16_9_x' => (int) $data['crop_16_9_x'],
            'crop_16_9_y' => (int) $data['crop_16_9_y'],
            'crop_16_9_width' => (int) $data['crop_16_9_width'],
            'crop_16_9_height' => (int) $data['crop_16_9_height'],
            'crop_4_3_x' => (int) $data['crop_4_3_x'],
            'crop_4_3_y' => (int) $data['crop_4_3_y'],
            'crop_4_3_width' => (int) $data['crop_4_3_width'],
            'crop_4_3_height' => (int) $data['crop_4_3_height'],
            ...$pathMap,
        ]);

        $this->syncTags($media, $data['tags'] ?? []);

        return $media->load(['category', 'tags']);
    }

    public function delete(Media $media): void
    {
        $this->mediaRepository->update($media, [
            'file_cleanup_marked_at' => now(),
        ]);

        if (!$this->mediaRepository->delete($media)) {
            throw new ApiException('Failed to delete the media item.', 500);
        }
    }

    public function update(Media $media, array $data): Media
    {
        $disk = Storage::disk($this->mediaDisk());
        $manager = $this->makeImageManager();

        try {
            $originalImagePath = $disk->path($media->original_path);

            $updatedPayload = [
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->makeUniqueSlug($data['title'], $media),
                'alt_text' => $data['alt_text'],
                'caption' => $data['caption'] ?? null,
                'description' => $data['description'] ?? null,
                'crop_16_9_x' => (int) $data['crop_16_9_x'],
                'crop_16_9_y' => (int) $data['crop_16_9_y'],
                'crop_16_9_width' => (int) $data['crop_16_9_width'],
                'crop_16_9_height' => (int) $data['crop_16_9_height'],
                'crop_4_3_x' => (int) $data['crop_4_3_x'],
                'crop_4_3_y' => (int) $data['crop_4_3_y'],
                'crop_4_3_width' => (int) $data['crop_4_3_width'],
                'crop_4_3_height' => (int) $data['crop_4_3_height'],
                ...$this->storeVariantFromPath($manager, $originalImagePath, $data, '16x9', 'medium', $media->ratio_16_9_medium_path),
                ...$this->storeVariantFromPath($manager, $originalImagePath, $data, '16x9', 'big', $media->ratio_16_9_big_path),
                ...$this->storeVariantFromPath($manager, $originalImagePath, $data, '4x3', 'medium', $media->ratio_4_3_medium_path),
                ...$this->storeVariantFromPath($manager, $originalImagePath, $data, '4x3', 'big', $media->ratio_4_3_big_path),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            throw new ApiException('Failed to update the media item.', 422);
        }

        if (!$this->mediaRepository->update($media, $updatedPayload)) {
            throw new ApiException('Failed to update the media item.', 500);
        }

        $this->syncTags($media, $data['tags'] ?? []);

        return $media->refresh()->load(['category', 'tags']);
    }

    protected function storeVariant(
        ImageManager $manager,
        UploadedFile $uploadedFile,
        array $data,
        string $ratioKey,
        string $sizeKey,
        string $baseDirectory
    ): array {
        $image = $manager->read($uploadedFile->getRealPath());
        $crop = $this->normalizeCropData(
            $this->extractCropData($data, $ratioKey),
            $image
        );
        $size = $this->mediaSize($ratioKey, $sizeKey);

        $image->crop(
            $crop['width'],
            $crop['height'],
            $crop['x'],
            $crop['y']
        );

        $image->resize($size['width'], $size['height']);

        $filename = "{$ratioKey}-{$sizeKey}.webp";
        $path = "{$baseDirectory}/{$filename}";
        $encoded = (string) $image->toWebp($this->mediaQuality());

        Storage::disk($this->mediaDisk())->put($path, $encoded);

        return [
            "{$this->variantColumnPrefix($ratioKey, $sizeKey)}_path" => $path,
            "{$this->variantColumnPrefix($ratioKey, $sizeKey)}_size" => strlen($encoded),
        ];
    }

    protected function storeVariantFromPath(
        ImageManager $manager,
        string $sourceImagePath,
        array $data,
        string $ratioKey,
        string $sizeKey,
        string $path
    ): array {
        $image = $manager->read($sourceImagePath);
        $crop = $this->normalizeCropData(
            $this->extractCropData($data, $ratioKey),
            $image
        );
        $size = $this->mediaSize($ratioKey, $sizeKey);

        $image->crop(
            $crop['width'],
            $crop['height'],
            $crop['x'],
            $crop['y']
        );

        $image->resize($size['width'], $size['height']);

        $encoded = (string) $image->toWebp($this->mediaQuality());

        Storage::disk($this->mediaDisk())->put($path, $encoded);

        return [
            "{$this->variantColumnPrefix($ratioKey, $sizeKey)}_path" => $path,
            "{$this->variantColumnPrefix($ratioKey, $sizeKey)}_size" => strlen($encoded),
        ];
    }

    protected function normalizeCropData(array $crop, ImageInterface $image): array
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();

        $x = max(0, min($crop['x'], max(0, $imageWidth - 1)));
        $y = max(0, min($crop['y'], max(0, $imageHeight - 1)));
        $width = max(1, min($crop['width'], $imageWidth - $x));
        $height = max(1, min($crop['height'], $imageHeight - $y));

        return [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];
    }

    protected function extractCropData(array $data, string $ratioKey): array
    {
        $prefix = $ratioKey === '16x9' ? 'crop_16_9' : 'crop_4_3';

        return [
            'x' => (int) $data["{$prefix}_x"],
            'y' => (int) $data["{$prefix}_y"],
            'width' => (int) $data["{$prefix}_width"],
            'height' => (int) $data["{$prefix}_height"],
        ];
    }

    protected function buildBaseDirectory(string $uuid): string
    {
        return sprintf(
            '%s/%s/%s/%s/%s',
            trim(config('media.base_directory', 'media'), '/'),
            now()->format('Y'),
            now()->format('m'),
            now()->format('d'),
            $uuid
        );
    }

    protected function mediaQuality(): int
    {
        return (int) config('media.quality', 80);
    }

    protected function mediaDisk(): string
    {
        return (string) config('media.disk', 'public');
    }

    protected function mediaSize(string $ratioKey, string $sizeKey): array
    {
        $configuredSize = config("media.sizes.{$ratioKey}.{$sizeKey}");
        if (is_array($configuredSize) && isset($configuredSize['width'], $configuredSize['height'])) {
            return [
                'width' => (int) $configuredSize['width'],
                'height' => (int) $configuredSize['height'],
            ];
        }

        $fallbacks = [
            '16x9' => [
                'medium' => ['width' => 800, 'height' => 450],
                'big' => ['width' => 1600, 'height' => 900],
            ],
            '4x3' => [
                'medium' => ['width' => 800, 'height' => 600],
                'big' => ['width' => 1600, 'height' => 1200],
            ],
        ];

        return $fallbacks[$ratioKey][$sizeKey];
    }

    protected function variantColumnPrefix(string $ratioKey, string $sizeKey): string
    {
        $normalizedRatio = $ratioKey === '16x9' ? '16_9' : '4_3';

        return "ratio_{$normalizedRatio}_{$sizeKey}";
    }

    protected function makeUniqueSlug(string $title, ?Media $ignoreMedia = null): string
    {
        $baseSlug = Str::slug($title);
        $fallbackSlug = $baseSlug !== '' ? $baseSlug : Str::random(12);
        $slug = $fallbackSlug;
        $suffix = 1;

        while (Media::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreMedia, fn ($query) => $query->where('id', '!=', $ignoreMedia->id))
            ->exists()) {
            $slug = "{$fallbackSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected function makeImageManager(): ImageManager
    {
        $driver = extension_loaded('imagick')
            ? new ImagickDriver()
            : new GdDriver();

        return new ImageManager($driver);
    }

    protected function syncTags(Media $media, array $tags): void
    {
        $normalizedTags = collect($tags)
            ->map(fn ($tag) => is_string($tag) ? trim($tag) : '')
            ->filter()
            ->unique(fn (string $tag) => Str::lower($tag))
            ->values();

        if ($normalizedTags->isEmpty()) {
            $media->tags()->sync([]);

            return;
        }

        $tagIds = $normalizedTags->map(function (string $tagName) {
            $slug = Str::slug($tagName);

            return Tag::query()->firstOrCreate(
                ['slug' => $slug !== '' ? $slug : Str::random(12)],
                ['name' => $tagName]
            )->id;
        });

        $media->tags()->sync($tagIds->all());
    }
}
