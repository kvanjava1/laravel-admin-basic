<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articleGroup = CategoryGroup::where('slug', 'artikel')->first();
        
        if (!$articleGroup) return;

        $categories = [
            'News',
            'Technology',
            'Lifestyle',
            'Business',
            'Education'
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'category_group_id' => $articleGroup->id,
                ]
            );
        }
    }
}
