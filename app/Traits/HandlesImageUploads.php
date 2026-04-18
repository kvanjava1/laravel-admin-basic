<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Exception;

trait HandlesImageUploads
{
    /**
     * Store and optionally crop an uploaded image.
     *
     * @param UploadedFile $file
     * @param array|null $crop
     * @param string $directory
     * @return string|null
     */
    protected function storeProfileImage(UploadedFile $file, ?array $crop = null, string $directory = 'profile_pictures')
    {
        try {
            // 1. Initialize Image Manager (V3)
            $driver = extension_loaded('imagick')
                ? new ImagickDriver()
                : new GdDriver();

            $manager = new ImageManager($driver);
            $image = $manager->read($file->getRealPath());

            // 2. Apply Server-Side Crop if coordinates provided
            if ($crop) {
                $image->crop(
                    (int) $crop['width'],
                    (int) $crop['height'],
                    (int) $crop['x'],
                    (int) $crop['y']
                );
            }

            // 3. Scale to standard size (400x400)
            $image->scale(400, 400);

            // 4. Encode to WebP
            $encoded = $image->toWebp(80);

            // 5. Save to Storage
            $year = date('Y');
            $month = date('m');
            $day = date('d');

            $fileName = Str::random(20) . '.webp';
            $path = "{$directory}/{$year}/{$month}/{$day}/{$fileName}";

            Storage::disk('public')->put($path, (string) $encoded);

            return $path;
        } catch (Exception $e) {
            Log::error('Image processing failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a profile image from storage.
     *
     * @param string|null $path
     * @return bool
     */
    protected function deleteProfileImage(?string $path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
}
