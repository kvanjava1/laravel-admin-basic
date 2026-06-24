<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['slug' => 'view-users',    'name' => 'view users',    'group' => 'User Management'],
            ['slug' => 'create-users',  'name' => 'create users',  'group' => 'User Management'],
            ['slug' => 'edit-users',    'name' => 'edit users',    'group' => 'User Management'],
            ['slug' => 'delete-users',  'name' => 'delete users',  'group' => 'User Management'],
            ['slug' => 'govern-users',  'name' => 'govern users',  'group' => 'User Management'],

            ['slug' => 'view-roles',    'name' => 'view roles',    'group' => 'Role Management'],
            ['slug' => 'create-roles',  'name' => 'create roles',  'group' => 'Role Management'],
            ['slug' => 'edit-roles',    'name' => 'edit roles',    'group' => 'Role Management'],
            ['slug' => 'delete-roles',  'name' => 'delete roles',  'group' => 'Role Management'],

            ['slug' => 'view-categories',    'name' => 'view categories',    'group' => 'Category Management'],
            ['slug' => 'create-categories',  'name' => 'create categories',  'group' => 'Category Management'],
            ['slug' => 'edit-categories',    'name' => 'edit categories',    'group' => 'Category Management'],
            ['slug' => 'delete-categories',  'name' => 'delete categories',  'group' => 'Category Management'],

            ['slug' => 'view-media',    'name' => 'view media',    'group' => 'Media Management'],
            ['slug' => 'create-media',  'name' => 'create media',  'group' => 'Media Management'],
            ['slug' => 'edit-media',    'name' => 'edit media',    'group' => 'Media Management'],
            ['slug' => 'delete-media',  'name' => 'delete media',  'group' => 'Media Management'],

            ['slug' => 'view-articles',    'name' => 'view articles',    'group' => 'Article Management'],
            ['slug' => 'create-articles',  'name' => 'create articles',  'group' => 'Article Management'],
            ['slug' => 'edit-articles',    'name' => 'edit articles',    'group' => 'Article Management'],
            ['slug' => 'delete-articles',  'name' => 'delete articles',  'group' => 'Article Management'],
        ];

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

        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
