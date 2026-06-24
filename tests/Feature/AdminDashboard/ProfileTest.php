<?php

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    UserStatus::create(['name' => 'Active']);

    $this->user = User::factory()->create([
        'email' => 'profile@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    Sanctum::actingAs($this->user);
});

test('can view own profile', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'profile@example.com');
});

test('can update profile name and email', function () {
    $response = $this->putJson('/api/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
});

test('can update profile with new password', function () {
    $response = $this->putJson('/api/profile', [
        'name' => 'With Password',
        'email' => 'profile@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('profile update with password mismatch fails', function () {
    $response = $this->putJson('/api/profile', [
        'name' => 'Mismatch',
        'email' => 'profile@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422);
});
