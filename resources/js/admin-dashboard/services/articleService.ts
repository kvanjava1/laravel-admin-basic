import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';

/**
 * articleService.ts
 * Service layer for Article-related API calls.
 */

const { get, post, put, delete: destroy } = useApi();

export const articleService = {
    /**
     * Get paginated articles
     */
    async getPaginated(params: any = {}) {
        return await get(route('articles.index'), { params });
    },

    /**
     * Store a new article
     */
    async store(data: any) {
        return await post(route('articles.store'), data);
    },

    /**
     * Show single article
     */
    async show(id: number) {
        return await get(route('articles.show', id));
    },

    /**
     * Update an existing article
     */
    async update(id: number, data: any) {
        return await put(route('articles.update', id), data);
    },

    /**
     * Delete an article
     */
    async destroy(id: number) {
        return await destroy(route('articles.destroy', id));
    },

    /**
     * Get all possible article statuses
     */
    async getStatuses() {
        // Return static statuses for consistency if no dedicated endpoint
        return {
            data: [
                { id: 'draft', name: 'Draft', label: 'Draft', color_class: 'text-slate-500' },
                { id: 'published', name: 'Published', label: 'Published', color_class: 'text-green-600' },
                { id: 'scheduled', name: 'Scheduled', label: 'Scheduled', color_class: 'text-blue-600' },
            ]
        };
    }
};
