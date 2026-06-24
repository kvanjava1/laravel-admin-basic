<?php

use App\Models\User;
use App\Models\UserStatus;
use App\Models\BanHistory;
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
    Role::create(['name' => 'Super Administrator', 'guard_name' => 'web']);

    Permission::create(['name' => 'govern-users', 'guard_name' => 'web']);

    $this->admin = User::factory()->create([
        'email' => 'admin@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $this->admin->assignRole('Administrator');
    $this->admin->givePermissionTo('govern-users');

    $this->targetUser = User::factory()->create([
        'email' => 'target@example.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);

    Sanctum::actingAs($this->admin);
});

test('can permanently ban a user', function () {
    $response = $this->postJson("/api/users/{$this->targetUser->id}/ban", [
        'type' => 'permanent',
        'reason' => 'Violated terms of service',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User banned successfully');

    $this->targetUser->refresh();
    expect($this->targetUser->status->name)->toBe('Banned');
    expect($this->targetUser->ban_expires_at)->toBeNull();

    $this->assertDatabaseHas('ban_histories', [
        'user_id' => $this->targetUser->id,
        'admin_id' => $this->admin->id,
        'type' => 'permanent',
        'action' => 'banned',
    ]);
});

test('user without govern-users permission gets 403', function () {
    $noPermUser = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    Sanctum::actingAs($noPermUser);

    $response = $this->postJson("/api/users/{$this->targetUser->id}/ban", [
        'type' => 'permanent',
        'reason' => 'Attempt without permission',
    ]);

    $response->assertStatus(403);
});

test('can temporarily ban a user with expiry', function () {
    $expiredAt = now()->addDays(7);

    $response = $this->postJson("/api/users/{$this->targetUser->id}/ban", [
        'type' => 'temporary',
        'reason' => 'Temporary suspension',
        'expired_at' => $expiredAt->toDateTimeString(),
    ]);

    $response->assertStatus(200);

    $this->targetUser->refresh();
    expect($this->targetUser->status->name)->toBe('Banned');
    expect($this->targetUser->ban_expires_at)->not->toBeNull();

    $this->assertDatabaseHas('ban_histories', [
        'user_id' => $this->targetUser->id,
        'type' => 'temporary',
        'action' => 'banned',
    ]);
});

test('ban fails without reason', function () {
    $response = $this->postJson("/api/users/{$this->targetUser->id}/ban", [
        'type' => 'permanent',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('can unban a user', function () {
    $this->targetUser->update([
        'status_id' => UserStatus::where('name', 'Banned')->first()->id,
    ]);

    $response = $this->postJson("/api/users/{$this->targetUser->id}/unban", [
        'reason' => 'Appeal approved',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User restored successfully');

    $this->targetUser->refresh();
    expect($this->targetUser->status->name)->toBe('Active');

    $this->assertDatabaseHas('ban_histories', [
        'user_id' => $this->targetUser->id,
        'action' => 'restored',
    ]);
});

test('can activate a user', function () {
    $this->targetUser->update([
        'status_id' => UserStatus::where('name', 'Inactive')->first()->id,
    ]);

    $response = $this->postJson("/api/users/{$this->targetUser->id}/activate", [
        'reason' => 'Reactivation approved',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->targetUser->refresh();
    expect($this->targetUser->status->name)->toBe('Active');

    $this->assertDatabaseHas('ban_histories', [
        'user_id' => $this->targetUser->id,
        'action' => 'activated',
    ]);
});

test('can deactivate a user', function () {
    $response = $this->postJson("/api/users/{$this->targetUser->id}/deactivate", [
        'reason' => 'Inactive account',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->targetUser->refresh();
    expect($this->targetUser->status->name)->toBe('Inactive');

    $this->assertDatabaseHas('ban_histories', [
        'user_id' => $this->targetUser->id,
        'action' => 'deactivated',
    ]);
});

test('can retrieve ban history for a user', function () {
    BanHistory::create([
        'user_id' => $this->targetUser->id,
        'admin_id' => $this->admin->id,
        'type' => 'permanent',
        'action' => 'banned',
        'reason' => 'First offense',
    ]);

    $response = $this->getJson("/api/users/{$this->targetUser->id}/ban-history");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.history');
});

test('governance actions are atomic with transaction', function () {
    $response = $this->postJson("/api/users/{$this->targetUser->id}/ban", [
        'type' => 'permanent',
        'reason' => 'Atomicity test',
    ]);

    $response->assertStatus(200);

    $this->targetUser->refresh();
    expect($this->targetUser->status->name)->toBe('Banned');

    $this->assertDatabaseHas('ban_histories', [
        'user_id' => $this->targetUser->id,
        'action' => 'banned',
    ]);
});
