<?php

/**
 * ArticleManagementTest.php
 * Feature tests for Article management with SAFE manual cleanup.
 * This test file avoids RefreshDatabase to protect existing development data.
 */

use App\Models\Article;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->createdArticleIds = [];
    $this->createdTagNames = [];
    $this->createdMediaIds = [];
    
    // Ensure we have a valid media ID to use for tests
    $media = Media::first();
    if (!$media) {
        $media = Media::create([
            'uuid' => (string) str()->uuid(),
            'title' => 'Test Media',
            'slug' => 'test-media-' . str()->random(5),
            'alt_text' => 'Test alt',
            'original_path' => 'media/test.webp',
            'output_mime_type' => 'image/webp',
        ]);
        $this->createdMediaIds[] = $media->id;
    }
    $this->testMediaId = $media->id;
});

afterEach(function () {
    if (!empty($this->createdArticleIds)) {
        Article::query()->withTrashed()->whereIn('id', $this->createdArticleIds)->forceDelete();
    }

    if (!empty($this->createdTagNames)) {
        Tag::query()->whereIn('name', $this->createdTagNames)->delete();
    }
    
    if (!empty($this->createdMediaIds)) {
        Media::query()->whereIn('id', $this->createdMediaIds)->forceDelete();
    }
});

test('authenticated admins can list articles', function () {
    $admin = User::first();
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/articles');

    $response->assertStatus(200);
});

test('authenticated admins can create a new article with tags', function () {
    $admin = User::first();
    Sanctum::actingAs($admin);
    
    $category = Category::first();

    $title = 'Test Article ' . str()->random(8);
    $tagName = 'test-tag-' . str()->random(5);
    $this->createdTagNames[] = $tagName;

    $payload = [
        'title' => $title,
        'slug' => str($title)->slug()->value(),
        'excerpt' => 'This is a test excerpt created by automation.',
        'content' => '<h2>Testing Content</h2><p>Safe testing without refresh database.</p>',
        'category_id' => $category->id,
        'status' => 'published',
        'tags' => [$tagName],
        'featured_image_id' => $this->testMediaId,
        'seo_title' => $title,
        'seo_description' => 'Test SEO description.',
        'seo_focus_keyword' => 'Test',
    ];

    $response = $this->postJson('/api/articles', $payload);

    $response->assertStatus(201);
    
    $articleId = $response->json('article.id');
    if ($articleId) {
        $this->createdArticleIds[] = $articleId;
    }

    $this->assertDatabaseHas('articles', ['id' => $articleId, 'title' => $title]);
});

test('article creation fails when required fields are missing', function () {
    $admin = User::first();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/articles', [
        'title' => '', 
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['title', 'content', 'category_id']);
});

test('authenticated admins can update an article', function () {
    $admin = User::first();
    Sanctum::actingAs($admin);
    $category = Category::first();

    $article = Article::create([
        'author_id' => $admin->id,
        'category_id' => $category->id,
        'title' => 'Original Title ' . str()->random(5),
        'slug' => 'original-title-' . str()->random(5),
        'content' => 'Original content',
        'excerpt' => 'Original excerpt',
        'status' => 'draft',
    ]);
    $this->createdArticleIds[] = $article->id;

    $updatedTitle = 'Updated Title ' . str()->random(5);
    $response = $this->putJson("/api/articles/{$article->id}", [
        'title' => $updatedTitle,
        'slug' => str($updatedTitle)->slug()->value(),
        'content' => 'Updated content',
        'excerpt' => 'Updated excerpt',
        'category_id' => $category->id,
        'status' => 'published',
        'featured_image_id' => $this->testMediaId,
        'tags' => ['updated'],
        'seo_title' => $updatedTitle,
        'seo_description' => 'Updated desc',
        'seo_focus_keyword' => 'Update',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => $updatedTitle]);
});

test('authenticated admins can soft delete an article', function () {
    $admin = User::first();
    Sanctum::actingAs($admin);
    $category = Category::first();

    $article = Article::create([
        'author_id' => $admin->id,
        'category_id' => $category->id,
        'title' => 'Delete Me ' . str()->random(5),
        'slug' => 'delete-me-' . str()->random(5),
        'content' => 'Delete content',
        'excerpt' => 'Delete excerpt',
    ]);
    $this->createdArticleIds[] = $article->id;

    $response = $this->deleteJson("/api/articles/{$article->id}");

    $response->assertStatus(200);
    
    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});
