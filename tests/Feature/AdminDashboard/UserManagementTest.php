<?php

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    UserStatus::create(['name' => 'Active']);
    UserStatus::create(['name' => 'Inactive']);
    UserStatus::create(['name' => 'Banned']);

    Role::create(['name' => 'Administrator', 'guard_name' => 'web']);

    $userPerms = ['view-users', 'create-users', 'edit-users', 'delete-users'];
    foreach ($userPerms as $perm) {
        Permission::create(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create([
        'email' => 'admin@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $this->admin->assignRole('Administrator');
    $this->admin->givePermissionTo($userPerms);

    Sanctum::actingAs($this->admin);
});

test('can list users with pagination', function () {
    User::factory()->count(3)->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Users retrieved successfully');
});

test('user without view-users permission gets 403', function () {
    $noPermUser = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    Sanctum::actingAs($noPermUser);

    $response = $this->getJson('/api/users');

    $response->assertStatus(403);
});

test('can filter users by search term', function () {
    User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    User::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $response = $this->getJson('/api/users?search=John');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.data.0.name', 'John Doe');
});

test('can create a new user', function () {
    $response = $this->postJson('/api/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'Administrator',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User created successfully')
        ->assertJsonPath('data.name', 'New User');

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

test('cannot create user with banned status', function () {
    $response = $this->postJson('/api/users', [
        'name' => 'Banned User',
        'email' => 'bannednew@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'Administrator',
        'status_id' => UserStatus::where('name', 'Banned')->first()->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('can show a specific user', function () {
    $target = User::factory()->create([
        'email' => 'target@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $target->assignRole('Administrator');

    $response = $this->getJson("/api/users/{$target->id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'target@example.com')
        ->assertJsonPath('data.roles.0.name', 'Administrator');
});

test('can update a user', function () {
    $target = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $target->assignRole('Administrator');

    $response = $this->putJson("/api/users/{$target->id}", [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'Administrator',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Updated Name']);
});

test('status_id is stripped from user update payload', function () {
    $target = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $target->assignRole('Administrator');

    $response = $this->putJson("/api/users/{$target->id}", [
        'name' => 'Test',
        'email' => $target->email,
        'status_id' => UserStatus::where('name', 'Banned')->first()->id,
        'role' => 'Administrator',
    ]);

    $response->assertStatus(200);

    $target->refresh();
    expect($target->status->name)->toBe('Active');
});

test('can soft delete a user', function () {
    $target = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $response = $this->deleteJson("/api/users/{$target->id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User deleted successfully');

    $this->assertSoftDeleted('users', ['id' => $target->id]);
});
