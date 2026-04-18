<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\CategoryGroup;

class CategoryRepository
{
    /**
     * Get root categories with nested children for a group.
     */
    public function getTreeByGroupId($groupId)
    {
        return Category::where('category_group_id', $groupId)
            ->whereNull('parent_id')
            ->with(['childrenRecursive' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all active category groups.
     */
    public function getActiveGroups()
    {
        return CategoryGroup::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new category.
     */
    public function create(array $data)
    {
        return Category::create($data);
    }

    /**
     * Update an existing category.
     */
    public function update(Category $category, array $data)
    {
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category)
    {
        return $category->delete();
    }
}
