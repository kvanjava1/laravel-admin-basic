<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ["name" => "Artikel", "slug" => "artikel"],
            ["name" => "Image", "slug" => "image"],
        ];

        foreach ($groups as $group) {
            \App\Models\CategoryGroup::updateOrCreate(
                ["slug" => $group["slug"]],
                $group,
            );
        }
    }
}
