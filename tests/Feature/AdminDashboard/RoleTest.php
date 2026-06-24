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

    Role::create(['name' => 'Super Administrator', 'guard_name' => 'web']);
    Role::create(['name' => 'Administrator', 'guard_name' => 'web']);

    $rolePerms = ['view-roles', 'create-roles', 'edit-roles', 'delete-roles'];
    foreach ($rolePerms as $perm) {
        Permission::create(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->superAdmin = User::factory()->create([
        'email' => 'admin@admin.com',
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    $this->superAdmin->assignRole('Super Administrator');
    $this->superAdmin->givePermissionTo($rolePerms);

    Sanctum::actingAs($this->superAdmin);
});

test('can list roles with pagination', function () {
    $response = $this->getJson('/api/roles');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Roles retrieved successfully');
});

test('user without view-roles permission gets 403', function () {
    $noPermUser = User::factory()->create([
        'status_id' => UserStatus::where('name', 'Active')->first()->id,
    ]);
    Sanctum::actingAs($noPermUser);

    $response = $this->getJson('/api/roles');

    $response->assertStatus(403);
});

test('can create a role with permissions', function () {
    Permission::create(['name' => 'view-users', 'guard_name' => 'web']);

    $response = $this->postJson('/api/roles', [
        'name' => 'Editor',
        'permissions' => ['view-users'],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Role created successfully')
        ->assertJsonPath('data.name', 'Editor');

    $this->assertDatabaseHas('roles', ['name' => 'Editor']);
});

test('can show a specific role', function () {
    $role = Role::where('name', 'Administrator')->first();

    $response = $this->getJson("/api/roles/{$role->id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Administrator');
});

test('can update a role and sync permissions', function () {
    Permission::create(['name' => 'view-users', 'guard_name' => 'web']);
    Permission::create(['name' => 'create-users', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'Moderator', 'guard_name' => 'web']);

    $response = $this->putJson("/api/roles/{$role->id}", [
        'name' => 'Senior Moderator',
        'permissions' => ['view-users', 'create-users'],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Senior Moderator');

    $role->refresh();
    expect($role->hasPermissionTo('view-users'))->toBeTrue();
    expect($role->hasPermissionTo('create-users'))->toBeTrue();
});

test('cannot delete Super Administrator', function () {
    $role = Role::where('name', 'Super Administrator')->first();

    $response = $this->deleteJson("/api/roles/{$role->id}");

    $response->assertStatus(403)
        ->assertJsonPath('success', false);
});

test('can get role options for dropdowns', function () {
    $response = $this->getJson('/api/roles/options');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});
