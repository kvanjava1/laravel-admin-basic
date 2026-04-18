<?php

use App\Models\Tag;
use App\Models\Media;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->createdMediaIds = [];
    $this->createdTagSlugs = [];
    $this->createdUserIds = [];
    $this->createdCategoryIds = [];
});

afterEach(function () {
    if (!empty($this->createdMediaIds)) {
        Media::query()->withTrashed()->whereIn('id', $this->createdMediaIds)->get()->each->forceDelete();
    }

    if (!empty($this->createdTagSlugs)) {
        Tag::query()->whereIn('slug', $this->createdTagSlugs)->delete();
    }

    if (!empty($this->createdCategoryIds)) {
        Category::query()->whereIn('id', $this->createdCategoryIds)->delete();
    }

    if (!empty($this->createdUserIds)) {
        User::query()->whereIn('id', $this->createdUserIds)->delete();
    }
});

test('authenticated users can upload media and generated variants are stored', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);
    $title = 'Homepage Hero ' . str()->random(8);

    $response = $this->post('/api/media', mediaUploadPayload([
        'title' => $title,
        'tags' => ['hero', 'discover', 'Hero'],
    ]), [
        'Accept' => 'application/json',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media created successfully')
        ->assertJsonPath('data.title', $title);

    $media = Media::query()->where('title', $title)->latest('id')->first();
    $this->createdMediaIds[] = $media->id;

    expect($media)->not->toBeNull();
    expect($media->created_by)->toBe($user->id);
    expect($media->output_mime_type)->toBe('image/webp');
    expect($media->slug)->toStartWith(str($title)->slug()->value());
    expect($media->original_output_size)->toBeGreaterThan(0);
    expect($media->ratio_16_9_medium_size)->toBeGreaterThan(0);
    expect($media->ratio_16_9_big_size)->toBeGreaterThan(0);
    expect($media->ratio_4_3_medium_size)->toBeGreaterThan(0);
    expect($media->ratio_4_3_big_size)->toBeGreaterThan(0);
    expect($media->tags()->pluck('slug')->all())->toBe(['hero', 'discover']);
    expect(Tag::query()->whereIn('slug', ['hero', 'discover'])->count())->toBe(2);

    Storage::disk('public')->assertExists($media->original_path);
    Storage::disk('public')->assertExists($media->ratio_16_9_medium_path);
    Storage::disk('public')->assertExists($media->ratio_16_9_big_path);
    Storage::disk('public')->assertExists($media->ratio_4_3_medium_path);
    Storage::disk('public')->assertExists($media->ratio_4_3_big_path);

    expect($media->original_path)->toStartWith('media/');
    expect($media->ratio_16_9_medium_path)->toEndWith('16x9-medium.webp');
    expect($media->ratio_4_3_big_path)->toEndWith('4x3-big.webp');
});

test('media upload returns validation errors when required fields are missing', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);
    $mediaCountBefore = Media::count();

    $response = $this->postJson('/api/media', [
        'title' => '',
        'alt_text' => '',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors([
            'title',
            'alt_text',
            'image',
            'crop_16_9_x',
            'crop_16_9_y',
            'crop_16_9_width',
            'crop_16_9_height',
            'crop_4_3_x',
            'crop_4_3_y',
            'crop_4_3_width',
            'crop_4_3_height',
        ]);

    expect(Media::count())->toBe($mediaCountBefore);
    expect(Storage::disk('public')->allFiles())->toBe([]);
});

test('authenticated users can list media and filter by search term', function () {
    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);
    $searchToken = 'Breaking-' . str()->random(8);

    $matchingMedia = Media::create(mediaRecordAttributes([
        'title' => "{$searchToken} News Banner",
        'slug' => str($searchToken)->slug() . '-news-banner',
        'alt_text' => "{$searchToken} news banner",
    ]));
    $this->createdMediaIds[] = $matchingMedia->id;

    $otherMedia = Media::create(mediaRecordAttributes([
        'title' => 'Product Thumbnail',
        'slug' => 'product-thumbnail-' . str()->random(8),
        'alt_text' => 'Product thumbnail',
    ]));
    $this->createdMediaIds[] = $otherMedia->id;

    $response = $this->getJson('/api/media?title=' . urlencode($searchToken) . '&per_page=12');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media retrieved successfully')
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.id', $matchingMedia->id)
        ->assertJsonPath('data.data.0.title', "{$searchToken} News Banner")
        ->assertJsonPath('data.total', 1);
});

test('authenticated users can filter media by category and tags', function () {
    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);
    $uniqueTag = 'rizal-' . str()->random(8);

    $category = Category::create([
        'category_group_id' => 1,
        'parent_id' => null,
        'name' => 'Image Library',
        'slug' => 'image-library-' . str()->random(6),
        'is_active' => true,
    ]);
    $this->createdCategoryIds[] = $category->id;

    $matchingMedia = Media::create(mediaRecordAttributes([
        'title' => 'Filtered Media ' . str()->random(8),
        'slug' => 'filtered-media-' . str()->random(8),
        'category_id' => $category->id,
    ]));
    $this->createdMediaIds[] = $matchingMedia->id;
    $matchingTag = Tag::firstOrCreate(['slug' => $uniqueTag], ['name' => $uniqueTag]);
    $this->createdTagSlugs[] = $matchingTag->slug;
    $matchingMedia->tags()->sync([$matchingTag->id]);

    $otherMedia = Media::create(mediaRecordAttributes([
        'title' => 'Other Media ' . str()->random(8),
        'slug' => 'other-media-' . str()->random(8),
    ]));
    $this->createdMediaIds[] = $otherMedia->id;
    $testOtherTagSlug = 'other-tag-' . str()->random(8);
    $otherTag = Tag::firstOrCreate(['slug' => $testOtherTagSlug], ['name' => $testOtherTagSlug]);
    $this->createdTagSlugs[] = $otherTag->slug;
    $otherMedia->tags()->sync([$otherTag->id]);

    $response = $this->getJson('/api/media?category_id=' . $category->id . '&tags[]=' . urlencode($uniqueTag) . '&per_page=12');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.data.0.id', $matchingMedia->id);
});

test('media detail includes tags loaded from relation', function () {
    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);

    $media = Media::create(mediaRecordAttributes([
        'title' => 'Tagged Media ' . str()->random(8),
        'slug' => 'tagged-media-' . str()->random(8),
    ]));
    $this->createdMediaIds[] = $media->id;

    $hero = Tag::firstOrCreate(['slug' => 'hero'], ['name' => 'Hero']);
    $discover = Tag::firstOrCreate(['slug' => 'discover'], ['name' => 'Discover']);
    $media->tags()->sync([$hero->id, $discover->id]);

    $response = $this->getJson("/api/media/{$media->id}");

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $media->id)
        ->assertJsonCount(2, 'data.tags')
        ->assertJsonPath('data.tags.0.slug', 'hero')
        ->assertJsonPath('data.tags.1.slug', 'discover');
});

test('authenticated users can retrieve tag options from existing tags', function () {
    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);
    $searchToken = 'riq-' . str()->random(8);
    $firstTag = "{$searchToken}-alpha";
    $secondTag = "{$searchToken}-beta";

    Tag::firstOrCreate(['slug' => $firstTag], ['name' => $firstTag]);
    Tag::firstOrCreate(['slug' => $secondTag], ['name' => $secondTag]);
    $this->createdTagSlugs[] = $firstTag;
    $this->createdTagSlugs[] = $secondTag;

    $response = $this->getJson('/api/tags/options?search=' . urlencode($searchToken) . '&limit=10');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Tag options retrieved successfully');

    $returnedSlugs = collect($response->json('data'))->pluck('slug')->all();

    expect($returnedSlugs)->toContain($firstTag, $secondTag);
    expect($returnedSlugs)->not->toContain('hero');
});

test('authenticated users can update media metadata, crops, and tags', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);

    $createResponse = $this->post('/api/media', mediaUploadPayload([
        'title' => 'Editable Media ' . str()->random(8),
        'tags' => ['before'],
    ]), [
        'Accept' => 'application/json',
    ]);

    $createResponse->assertCreated();

    $mediaId = $createResponse->json('data.id');
    $this->createdMediaIds[] = $mediaId;
    $media = Media::findOrFail($mediaId);

    $response = $this->putJson("/api/media/{$mediaId}", [
        'title' => 'Updated Media ' . str()->random(8),
        'alt_text' => 'Updated alt text',
        'caption' => 'Updated caption',
        'description' => 'Updated description',
        'category_id' => null,
        'tags' => ['feature', 'gallery', 'Feature'],
        'crop_16_9_x' => 120,
        'crop_16_9_y' => 80,
        'crop_16_9_width' => 1400,
        'crop_16_9_height' => 788,
        'crop_4_3_x' => 200,
        'crop_4_3_y' => 160,
        'crop_4_3_width' => 1000,
        'crop_4_3_height' => 750,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media updated successfully')
        ->assertJsonPath('data.alt_text', 'Updated alt text')
        ->assertJsonCount(2, 'data.tags');

    $media->refresh();

    expect($media->caption)->toBe('Updated caption');
    expect($media->description)->toBe('Updated description');
    expect($media->crop_16_9_x)->toBe(120);
    expect($media->crop_4_3_width)->toBe(1000);
    expect($media->ratio_16_9_medium_size)->toBeGreaterThan(0);
    expect($media->ratio_4_3_big_size)->toBeGreaterThan(0);
    expect($media->tags()->pluck('slug')->all())->toBe(['feature', 'gallery']);

    Storage::disk('public')->assertExists($media->ratio_16_9_medium_path);
    Storage::disk('public')->assertExists($media->ratio_4_3_big_path);
});

test('authenticated users can soft delete media and mark files for cleanup', function () {
    $user = User::factory()->create();
    $this->createdUserIds[] = $user->id;
    Sanctum::actingAs($user);

    $media = Media::create(mediaRecordAttributes([
        'title' => 'Delete Candidate ' . str()->random(8),
        'slug' => 'delete-candidate-' . str()->random(8),
    ]));

    $response = $this->deleteJson("/api/media/{$media->id}");

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media deleted successfully');

    $trashedMedia = Media::withTrashed()->find($media->id);

    expect($trashedMedia)->not->toBeNull();
    expect($trashedMedia->trashed())->toBeTrue();
    expect($trashedMedia->file_cleanup_marked_at)->not->toBeNull();

    $trashedMedia->forceDelete();
});

function mediaUploadPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Homepage Hero',
        'alt_text' => 'Homepage hero image',
        'caption' => 'Homepage lead banner',
        'description' => 'Used on the homepage hero section.',
        'image' => UploadedFile::fake()->image('hero.jpg', 2400, 1800),
        'crop_16_9_x' => 100,
        'crop_16_9_y' => 120,
        'crop_16_9_width' => 1600,
        'crop_16_9_height' => 900,
        'crop_4_3_x' => 150,
        'crop_4_3_y' => 200,
        'crop_4_3_width' => 1200,
        'crop_4_3_height' => 900,
    ], $overrides);
}

function mediaRecordAttributes(array $overrides = []): array
{
    static $sequence = 1;

    $suffix = $sequence++;

    return array_merge([
        'uuid' => (string) fake()->uuid(),
        'category_id' => null,
        'created_by' => null,
        'title' => "Media {$suffix}",
        'slug' => "media-{$suffix}",
        'alt_text' => "Alt text {$suffix}",
        'caption' => "Caption {$suffix}",
        'description' => "Description {$suffix}",
        'original_path' => "media/2026/04/13/test-{$suffix}/original.webp",
        'ratio_16_9_medium_path' => "media/2026/04/13/test-{$suffix}/16x9-medium.webp",
        'ratio_16_9_big_path' => "media/2026/04/13/test-{$suffix}/16x9-big.webp",
        'ratio_4_3_medium_path' => "media/2026/04/13/test-{$suffix}/4x3-medium.webp",
        'ratio_4_3_big_path' => "media/2026/04/13/test-{$suffix}/4x3-big.webp",
        'original_mime_type' => 'image/jpeg',
        'output_mime_type' => 'image/webp',
        'original_size' => 123456,
        'original_output_size' => 120000,
        'ratio_16_9_medium_size' => 11000,
        'ratio_16_9_big_size' => 22000,
        'ratio_4_3_medium_size' => 33000,
        'ratio_4_3_big_size' => 44000,
        'crop_16_9_x' => 0,
        'crop_16_9_y' => 0,
        'crop_16_9_width' => 1600,
        'crop_16_9_height' => 900,
        'crop_4_3_x' => 0,
        'crop_4_3_y' => 0,
        'crop_4_3_width' => 1200,
        'crop_4_3_height' => 900,
    ], $overrides);
}
