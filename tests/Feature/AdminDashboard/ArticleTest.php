<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    UserStatus::create(['name' => 'Active']);

    CategoryGroup::create(['name' => 'Artikel', 'slug' => 'artikel', 'is_active' => true]);

    $this->category = Category::create([
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'News', 'slug' => 'news', 'is_active' => true,
    ]);

    $articlePerms = ['view-articles', 'create-articles', 'edit-articles', 'delete-articles'];
    foreach ($articlePerms as $perm) {
        Permission::create(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $this->admin->givePermissionTo($articlePerms);

    $this->media = Media::create([
        'uuid' => (string) fake()->uuid(),
        'title' => 'Featured Image',
        'slug' => 'featured-image-' . fake()->uuid(),
        'alt_text' => 'Featured',
        'original_path' => 'media/test/original.webp',
        'ratio_16_9_medium_path' => 'media/test/16x9-medium.webp',
        'ratio_16_9_big_path' => 'media/test/16x9-big.webp',
        'ratio_4_3_medium_path' => 'media/test/4x3-medium.webp',
        'ratio_4_3_big_path' => 'media/test/4x3-big.webp',
        'original_mime_type' => 'image/jpeg',
        'output_mime_type' => 'image/webp',
        'original_size' => 10000,
        'original_output_size' => 10000,
        'ratio_16_9_medium_size' => 5000,
        'ratio_16_9_big_size' => 5000,
        'ratio_4_3_medium_size' => 5000,
        'ratio_4_3_big_size' => 5000,
        'crop_16_9_x' => 0, 'crop_16_9_y' => 0,
        'crop_16_9_width' => 100, 'crop_16_9_height' => 100,
        'crop_4_3_x' => 0, 'crop_4_3_y' => 0,
        'crop_4_3_width' => 100, 'crop_4_3_height' => 100,
    ]);

    Sanctum::actingAs($this->admin);
});

test('can list articles', function () {
    $response = $this->getJson('/api/articles');

    $response->assertStatus(200);
});

test('can create an article with tags', function () {
    $title = 'Breaking News ' . fake()->uuid();

    $response = $this->postJson('/api/articles', [
        'title' => $title,
        'slug' => fake()->slug(),
        'content' => '<p>Article content here.</p>',
        'excerpt' => 'Short excerpt',
        'category_id' => $this->category->id,
        'status' => 'published',
        'featured_image_id' => $this->media->id,
        'tags' => ['news', 'breaking'],
        'seo_title' => $title,
        'seo_description' => 'SEO description for article',
        'seo_focus_keyword' => 'Breaking',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('message', 'Article created successfully')
        ->assertJsonPath('article.title', $title);

    $this->assertDatabaseHas('articles', ['title' => $title]);
});

test('article creation fails without required fields', function () {
    $response = $this->postJson('/api/articles', ['title' => '']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'content', 'category_id']);
});

test('can update an article', function () {
    $article = Article::create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Original Title',
        'slug' => 'original-title-' . fake()->uuid(),
        'content' => '<p>Original content</p>',
        'excerpt' => 'Original excerpt',
        'status' => 'draft',
    ]);

    $updatedTitle = 'Updated Article Title';

    $response = $this->putJson("/api/articles/{$article->id}", [
        'title' => $updatedTitle,
        'slug' => fake()->slug(),
        'content' => '<p>Updated content</p>',
        'excerpt' => 'Updated excerpt',
        'category_id' => $this->category->id,
        'status' => 'published',
        'featured_image_id' => $this->media->id,
        'tags' => ['updated'],
        'seo_title' => $updatedTitle,
        'seo_description' => 'Updated SEO',
        'seo_focus_keyword' => 'Update',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => $updatedTitle]);
});

test('publishing an article sets published_at automatically', function () {
    $article = Article::create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Draft Article',
        'slug' => 'draft-article-' . fake()->uuid(),
        'content' => '<p>Draft content</p>',
        'excerpt' => 'Draft excerpt',
        'status' => 'draft',
    ]);

    $this->putJson("/api/articles/{$article->id}", [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'excerpt' => $article->excerpt,
        'category_id' => $this->category->id,
        'status' => 'published',
        'featured_image_id' => $this->media->id,
        'tags' => ['test'],
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Desc',
        'seo_focus_keyword' => 'Test',
    ]);

    $article->refresh();
    expect($article->published_at)->not->toBeNull();
});

test('can soft delete an article', function () {
    $article = Article::create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Delete Me',
        'slug' => 'delete-me-' . fake()->uuid(),
        'content' => '<p>Delete content</p>',
        'excerpt' => 'Delete excerpt',
    ]);

    $response = $this->deleteJson("/api/articles/{$article->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});
