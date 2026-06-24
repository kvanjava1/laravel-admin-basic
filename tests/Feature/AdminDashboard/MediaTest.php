<?php

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function makeMediaUploadPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Test Image',
        'alt_text' => 'Test image alt text',
        'caption' => 'Test caption',
        'description' => 'Test description',
        'image' => UploadedFile::fake()->image('test.jpg', 2400, 1800),
        'crop_16_9_x' => 100,
        'crop_16_9_y' => 120,
        'crop_16_9_width' => 1600,
        'crop_16_9_height' => 900,
        'crop_4_3_x' => 150,
        'crop_4_3_y' => 200,
        'crop_4_3_width' => 1200,
        'crop_4_3_height' => 900,
        'tags' => ['hero'],
    ], $overrides);
}

function makeMediaRecordAttributes(array $overrides = []): array
{
    static $sequence = 1;
    $suffix = $sequence++;

    return array_merge([
        'uuid' => (string) fake()->uuid(),
        'category_id' => null,
        'title' => "Media {$suffix}",
        'slug' => "media-{$suffix}",
        'alt_text' => "Alt text {$suffix}",
        'original_path' => "media/test-{$suffix}/original.webp",
        'ratio_16_9_medium_path' => "media/test-{$suffix}/16x9-medium.webp",
        'ratio_16_9_big_path' => "media/test-{$suffix}/16x9-big.webp",
        'ratio_4_3_medium_path' => "media/test-{$suffix}/4x3-medium.webp",
        'ratio_4_3_big_path' => "media/test-{$suffix}/4x3-big.webp",
        'original_mime_type' => 'image/jpeg',
        'output_mime_type' => 'image/webp',
        'original_size' => 123456,
        'original_output_size' => 120000,
        'ratio_16_9_medium_size' => 11000,
        'ratio_16_9_big_size' => 22000,
        'ratio_4_3_medium_size' => 33000,
        'ratio_4_3_big_size' => 44000,
        'crop_16_9_x' => 0, 'crop_16_9_y' => 0,
        'crop_16_9_width' => 1600, 'crop_16_9_height' => 900,
        'crop_4_3_x' => 0, 'crop_4_3_y' => 0,
        'crop_4_3_width' => 1200, 'crop_4_3_height' => 900,
    ], $overrides);
}

beforeEach(function () {
    Storage::fake('public');

    UserStatus::create(['name' => 'Active']);

    CategoryGroup::create(['name' => 'Image', 'slug' => 'image', 'is_active' => true]);

    $mediaPerms = ['view-media', 'create-media', 'edit-media', 'delete-media'];
    foreach ($mediaPerms as $perm) {
        Permission::create(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $this->admin->givePermissionTo($mediaPerms);

    Sanctum::actingAs($this->admin);
});

test('can upload media and generates variants', function () {
    $response = $this->post('/api/media', makeMediaUploadPayload([
        'title' => 'Hero Banner',
    ]), ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media created successfully')
        ->assertJsonPath('data.title', 'Hero Banner');

    $media = Media::where('title', 'Hero Banner')->latest()->first();
    expect($media)->not->toBeNull();
    expect($media->output_mime_type)->toBe('image/webp');
    expect($media->original_output_size)->toBeGreaterThan(0);

    Storage::disk('public')->assertExists($media->original_path);
});

test('can list media with filters', function () {
    $uniqueTitle = 'Searchable-' . fake()->uuid();
    Media::create(makeMediaRecordAttributes([
        'title' => $uniqueTitle,
        'slug' => fake()->slug(),
    ]));
    Media::create(makeMediaRecordAttributes([
        'title' => 'Other Media',
        'slug' => fake()->slug(),
    ]));

    $response = $this->getJson('/api/media?title=' . urlencode($uniqueTitle));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total', 1)
        ->assertJsonPath('data.data.0.title', $uniqueTitle);
});

test('can show media detail with relations', function () {
    $media = Media::create(makeMediaRecordAttributes([
        'title' => 'Detail Media',
        'slug' => fake()->slug(),
    ]));
    $tag = Tag::create(['name' => 'DetailTag', 'slug' => 'detailtag']);
    $media->tags()->sync([$tag->id]);

    $response = $this->getJson("/api/media/{$media->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'Detail Media')
        ->assertJsonCount(1, 'data.tags');
});

test('can update media metadata and crops', function () {
    $createResponse = $this->post('/api/media', makeMediaUploadPayload([
        'title' => 'Editable Media',
    ]), ['Accept' => 'application/json']);

    $createResponse->assertCreated();
    $mediaId = $createResponse->json('data.id');
    $media = Media::findOrFail($mediaId);

    $response = $this->putJson("/api/media/{$media->id}", [
        'title' => 'Updated Title',
        'alt_text' => 'Updated alt',
        'caption' => 'Updated caption',
        'description' => 'Updated description',
        'tags' => ['feature', 'gallery'],
        'crop_16_9_x' => 200, 'crop_16_9_y' => 150,
        'crop_16_9_width' => 1400, 'crop_16_9_height' => 788,
        'crop_4_3_x' => 250, 'crop_4_3_y' => 180,
        'crop_4_3_width' => 1000, 'crop_4_3_height' => 750,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media updated successfully');

    $media->refresh();
    expect($media->title)->toBe('Updated Title');
    expect($media->alt_text)->toBe('Updated alt');
    expect($media->crop_16_9_x)->toBe(200);
});

test('media upload fails without required fields', function () {
    $response = $this->postJson('/api/media', ['title' => '']);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['title', 'alt_text', 'image']);
});

test('can soft delete media and marks cleanup', function () {
    $media = Media::create(makeMediaRecordAttributes([
        'title' => 'Delete Me',
        'slug' => fake()->slug(),
    ]));

    $response = $this->deleteJson("/api/media/{$media->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Media deleted successfully');

    $trashed = Media::withTrashed()->find($media->id);
    expect($trashed->trashed())->toBeTrue();
    expect($trashed->file_cleanup_marked_at)->not->toBeNull();
});
