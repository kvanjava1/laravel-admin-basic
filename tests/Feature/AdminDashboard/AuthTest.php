<?php

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    UserStatus::create(['name' => 'Active']);
    UserStatus::create(['name' => 'Inactive']);
    UserStatus::create(['name' => 'Banned']);

    Role::create(['name' => 'Administrator', 'guard_name' => 'web']);
});

test('login successful with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login successful')
        ->assertJsonPath('data.user.email', 'test@example.com')
        ->assertJsonPath('data.token', fn($token) => is_string($token) && strlen($token) > 0);
});

test('login response includes permissions', function () {
    $user = User::factory()->create([
        'email' => 'perm@example.com',
        'password' => 'password',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $user->assignRole('Administrator');
    Permission::create(['name' => 'view-users', 'guard_name' => 'web']);
    $user->givePermissionTo('view-users');

    $response = $this->postJson('/api/login', [
        'email' => 'perm@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.user.permissions.0.name', 'view-users');
});

test('login fails with wrong password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('login fails when user is banned', function () {
    $user = User::factory()->create([
        'email' => 'banned@example.com',
        'password' => 'password',
        'status_id' => UserStatus::where('name', 'Banned')->first()->id,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'banned@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', fn($msg) => str_contains($msg, 'banned'));
});

test('login fails when user is inactive', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => 'password',
        'status_id' => UserStatus::where('name', 'Inactive')->first()->id,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', fn($msg) => str_contains($msg, 'inactive'));
});

test('logout revokes current token', function () {
    $user = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $token = $loginResponse->json('data.token');

    $response = $this->withToken($token)->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logged out successfully');
});

test('authenticated user can get own profile via me', function () {
    $user = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $user->assignRole('Administrator');

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $token = $loginResponse->json('data.token');

    $response = $this->withToken($token)->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.roles.0.name', 'Administrator');
});

test('unauthenticated request returns 401', function () {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401)
        ->assertJsonPath('success', false);
});
