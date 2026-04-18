<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('alt_text');
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->string('original_path');
            $table->string('ratio_16_9_medium_path');
            $table->string('ratio_16_9_big_path');
            $table->string('ratio_4_3_medium_path');
            $table->string('ratio_4_3_big_path');
            $table->string('original_mime_type')->nullable();
            $table->string('output_mime_type')->default('image/webp');
            $table->unsignedBigInteger('original_size')->nullable();
            $table->integer('crop_16_9_x');
            $table->integer('crop_16_9_y');
            $table->integer('crop_16_9_width');
            $table->integer('crop_16_9_height');
            $table->integer('crop_4_3_x');
            $table->integer('crop_4_3_y');
            $table->integer('crop_4_3_width');
            $table->integer('crop_4_3_height');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
