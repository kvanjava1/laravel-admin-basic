<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserStatusSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\CategoryGroupSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserStatusSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            CategoryGroupSeeder::class,
        ]);
    }
}
