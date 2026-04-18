<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Exceptions\ApiException;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get category tree for a group.
     */
    public function getCategoryTree($groupId)
    {
        return $this->categoryRepository->getTreeByGroupId($groupId);
    }

    /**
     * Get all active category groups.
     */
    public function getAllGroups()
    {
        return $this->categoryRepository->getActiveGroups();
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data)
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Update an existing category with hierarchy protection.
     */
    public function updateCategory(Category $category, array $data)
    {
        // Hierarchy Protection: Ensure parent is not self
        if (isset($data['parent_id']) && $data['parent_id'] == $category->id) {
            throw new ApiException('A category cannot be its own parent.', 422);
        }

        // Logic check: ensure parent is not a descendant if we were doing deep moves, 
        // but for now the specific requirement was "cannot be its own parent".

        return $this->categoryRepository->update($category, $data);
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category)
    {
        return $this->categoryRepository->delete($category);
    }
}
