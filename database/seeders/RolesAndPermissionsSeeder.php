<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions with groups
        $permissions = [
            // USER MANAGEMENT
            ['name' => 'view users', 'slug' => 'view-users', 'group' => 'USER MANAGEMENT'],
            ['name' => 'create users', 'slug' => 'create-users', 'group' => 'USER MANAGEMENT'],
            ['name' => 'edit users', 'slug' => 'edit-users', 'group' => 'USER MANAGEMENT'],
            ['name' => 'delete users', 'slug' => 'delete-users', 'group' => 'USER MANAGEMENT'],
        ];

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['slug']],
                [
                    'guard_name' => 'web',
                    'group' => $permission['group'],
                    'display_name' => $permission['name'],
                ]
            );
        }

        // Create Roles and Assign Permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
