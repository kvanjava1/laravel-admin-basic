<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('original_output_size')->nullable()->after('original_size');
            $table->unsignedBigInteger('ratio_16_9_medium_size')->nullable()->after('ratio_16_9_medium_path');
            $table->unsignedBigInteger('ratio_16_9_big_size')->nullable()->after('ratio_16_9_big_path');
            $table->unsignedBigInteger('ratio_4_3_medium_size')->nullable()->after('ratio_4_3_medium_path');
            $table->unsignedBigInteger('ratio_4_3_big_size')->nullable()->after('ratio_4_3_big_path');
        });

        $disk = Storage::disk(config('media.disk', 'public'));

        DB::table('media')
            ->select([
                'id',
                'original_path',
                'ratio_16_9_medium_path',
                'ratio_16_9_big_path',
                'ratio_4_3_medium_path',
                'ratio_4_3_big_path',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($mediaItems) use ($disk): void {
                foreach ($mediaItems as $media) {
                    DB::table('media')
                        ->where('id', $media->id)
                        ->update([
                            'original_output_size' => $this->resolvePathSize($disk, $media->original_path),
                            'ratio_16_9_medium_size' => $this->resolvePathSize($disk, $media->ratio_16_9_medium_path),
                            'ratio_16_9_big_size' => $this->resolvePathSize($disk, $media->ratio_16_9_big_path),
                            'ratio_4_3_medium_size' => $this->resolvePathSize($disk, $media->ratio_4_3_medium_path),
                            'ratio_4_3_big_size' => $this->resolvePathSize($disk, $media->ratio_4_3_big_path),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'original_output_size',
                'ratio_16_9_medium_size',
                'ratio_16_9_big_size',
                'ratio_4_3_medium_size',
                'ratio_4_3_big_size',
            ]);
        });
    }

    protected function resolvePathSize($disk, ?string $path): int
    {
        if (!$path || !$disk->exists($path)) {
            return 0;
        }

        return (int) $disk->size($path);
    }
};
