import { useApi } from '../composables/useApi';

/**
 * categoryService.ts
 * Category-related API calls using the useApi utility.
 */

export interface Category {
    id: number;
    category_group_id: number;
    parent_id: number | null;
    name: string;
    slug: string;
    is_active: boolean;
    children_recursive?: Category[];
}

export interface CategoryGroup {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
}

export const categoryService = {
    /**
     * Get categories in tree structure for a specific group
     */
    async getCategories(groupId: number) {
        const { get } = useApi();
        try {
            const data = await get('/api/categories', { params: { group_id: groupId } });
            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Get all active category groups
     */
    async getGroups() {
        const { get } = useApi();
        try {
            const data = await get('/api/categories/groups');
            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Get a specific category
     */
    async getCategory(id: number | string) {
        const { get } = useApi();
        try {
            const data = await get(`/api/categories/${id}`);
            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Create a new category
     */
    async createCategory(categoryData: any) {
        const { post } = useApi();
        try {
            const data = await post('/api/categories', categoryData);
            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Update an existing category
     */
    async updateCategory(id: number | string, categoryData: any) {
        const { put } = useApi();
        try {
            const data = await put(`/api/categories/${id}`, categoryData);
            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Delete a category (Soft Delete)
     */
    async deleteCategory(id: number | string) {
        const api = useApi();
        try {
            const data = await api.delete(`/api/categories/${id}`);
            return data;
        } catch (error) {
            throw error;
        }
    }
};
