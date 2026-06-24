<?php

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    UserStatus::create(['name' => 'Active']);

    $catPerms = ['view-categories', 'create-categories', 'edit-categories', 'delete-categories'];
    foreach ($catPerms as $perm) {
        Permission::create(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $this->admin->givePermissionTo($catPerms);

    CategoryGroup::create(['name' => 'Artikel', 'slug' => 'artikel', 'is_active' => true]);
    CategoryGroup::create(['name' => 'Image', 'slug' => 'image', 'is_active' => true]);

    Sanctum::actingAs($this->admin);
});

test('can list category groups', function () {
    $response = $this->getJson('/api/categories/groups');

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');
});

test('can list categories as tree', function () {
    Category::create([
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'News', 'slug' => 'news', 'is_active' => true,
    ]);

    $response = $this->getJson('/api/categories?group_id=' . CategoryGroup::where('slug', 'artikel')->first()->id);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');
});

test('can create a category', function () {
    $response = $this->postJson('/api/categories', [
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'Technology', 'slug' => 'technology', 'is_active' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', 'Category created successfully');

    $this->assertDatabaseHas('categories', ['name' => 'Technology']);
});

test('can show a category', function () {
    $category = Category::create([
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'Sports', 'slug' => 'sports', 'is_active' => true,
    ]);

    $response = $this->getJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.name', 'Sports');
});

test('can update a category', function () {
    $category = Category::create([
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'Old Category', 'slug' => 'old-category', 'is_active' => true,
    ]);

    $response = $this->putJson("/api/categories/{$category->id}", [
        'category_group_id' => $category->category_group_id,
        'name' => 'Updated Category', 'slug' => 'updated-category', 'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.name', 'Updated Category');
});

test('cannot set category as its own parent', function () {
    $category = Category::create([
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'Root', 'slug' => 'root', 'is_active' => true,
    ]);

    $response = $this->putJson("/api/categories/{$category->id}", [
        'category_group_id' => $category->category_group_id,
        'name' => 'Root', 'slug' => 'root', 'is_active' => true,
        'parent_id' => $category->id,
    ]);

    $response->assertStatus(422);
});

test('can soft delete a category', function () {
    $category = Category::create([
        'category_group_id' => CategoryGroup::where('slug', 'artikel')->first()->id,
        'name' => 'Delete Me', 'slug' => 'delete-me', 'is_active' => true,
    ]);

    $response = $this->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});
