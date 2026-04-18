<?php

use App\Models\User;
use App\Models\UserStatus;
use App\Services\RoleAndAccountProtectionService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\{actingAs, postJson, putJson, deleteJson};

/**
 * @property string $protectedRole
 * @property string $protectedEmail
 * @property \App\Models\UserStatus $userStatus
 * @property \App\Models\User $superAdmin
 * @property \App\Models\User $admin
 */
beforeEach(function () {
    $this->protectedRole = config('protection.protected_roles')[0];
    $this->protectedEmail = config('protection.protected_accounts')[0];

    // Setup roles
    Role::create(['name' => $this->protectedRole]);
    Role::create(['name' => 'Administrator']);

    // Setup status
    $this->userStatus = UserStatus::create(['name' => 'Active']);

    // Setup users
    $this->superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => $this->protectedEmail,
        'password' => Hash::make('password'),
        'status_id' => $this->userStatus->id,
    ]);
    $this->superAdmin->assignRole($this->protectedRole);

    $this->admin = User::create([
        'name' => 'Regular Admin',
        'email' => 'admin2@admin.com',
        'password' => Hash::make('password'),
        'status_id' => $this->userStatus->id,
    ]);
    $this->admin->assignRole('Administrator');
});

it('blocks creating a user with protected system role', function () {
    actingAs($this->superAdmin);

    $response = postJson('/api/users', [
        'name' => 'New Super Admin',
        'email' => 'newsuper@admin.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'status_id' => $this->userStatus->id,
        'role' => $this->protectedRole,
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => "The '{$this->protectedRole}' role cannot be assigned through the dashboard."]);
});

it('blocks updating a user to protected system role', function () {
    actingAs($this->superAdmin);

    $response = putJson("/api/users/{$this->admin->id}", [
        'name' => 'Attempted Super Admin',
        'email' => $this->admin->email,
        'status_id' => $this->userStatus->id,
        'role' => $this->protectedRole,
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => "The '{$this->protectedRole}' role cannot be assigned through the dashboard."]);
});

it('blocks other admins from editing the protected super admin', function () {
    actingAs($this->admin);

    $response = putJson("/api/users/{$this->superAdmin->id}", [
        'name' => 'Malicious Edit',
        'email' => $this->superAdmin->email,
        'status_id' => $this->userStatus->id,
        'role' => 'Administrator',
    ]);

    // Should be blocked by authorization (403)
    $response->assertStatus(403);
});

it('allows the protected super admin to edit their own profile', function () {
    actingAs($this->superAdmin);

    $response = putJson("/api/users/{$this->superAdmin->id}", [
        'name' => 'Updated Name',
        'email' => $this->superAdmin->email,
        'status_id' => $this->userStatus->id,
        'role' => $this->protectedRole,
    ]);

    $response->assertStatus(200);
});

it('blocks updating a protected role name', function () {
    actingAs($this->superAdmin);

    $role = Role::findByName($this->protectedRole);

    $response = putJson("/api/roles/{$role->id}", [
        'name' => 'Renamed Role',
    ]);

    $response->assertStatus(403);
});

it('blocks deleting a protected role', function () {
    actingAs($this->superAdmin);

    $role = Role::findByName($this->protectedRole);

    $response = deleteJson("/api/roles/{$role->id}");

    $response->assertStatus(403);
});
