<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the 'Active' status ID
        $status = UserStatus::where('name', 'Active')->first();

        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('adminadmin'),
                'status_id' => $status ? $status->id : null,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Super Administrator');
    }
}
