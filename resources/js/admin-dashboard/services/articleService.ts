import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';
import { mockArticles } from '../mockup/articles';

/**
 * articleService.ts
 * Service layer for Article-related API calls.
 * Includes fallback to mockup data for initial development.
 */

const { get, post, delete: destroy } = useApi();
const useMockData = true; // Toggle this when backend is ready

export const articleService = {
    /**
     * Get paginated articles
     */
    async getPaginated(params: any = {}) {
        if (useMockData) {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 500));
            
            let filtered = [...mockArticles];
            
            // Basic filtering simulation for mockup
            if (params.search) {
                const s = params.search.toLowerCase();
                filtered = filtered.filter(a => 
                    a.title.toLowerCase().includes(s) || 
                    a.excerpt.toLowerCase().includes(s)
                );
            }

            if (params.status && params.status !== 'all') {
                filtered = filtered.filter(a => a.status === params.status);
            }

            return {
                data: filtered,
                meta: {
                    current_page: 1,
                    last_page: 1,
                    total: filtered.length,
                    per_page: 10
                }
            };
        }
        
        return await get(route('articles.index'), { params });
    },

    /**
     * Store a new article
     */
    async store(data: any) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            if (data[key] !== undefined && data[key] !== null) {
                formData.append(key, data[key]);
            }
        });

        return await post(route('articles.store'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    /**
     * Show single article
     */
    async show(id: number) {
        if (useMockData) {
            const article = mockArticles.find(a => a.id === id);
            return { data: article };
        }
        return await get(route('articles.show', id));
    },

    /**
     * Update an existing article
     */
    async update(id: number, data: any) {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        Object.keys(data).forEach(key => {
            if (data[key] !== undefined && data[key] !== null) {
                formData.append(key, data[key]);
            }
        });

        return await post(route('articles.update', id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    /**
     * Delete an article
     */
    async destroy(id: number) {
        if (useMockData) return { success: true };
        return await destroy(route('articles.destroy', id));
    },

    /**
     * Get all possible article statuses
     */
    async getStatuses() {
        if (useMockData) {
            return {
                data: [
                    { id: 1, name: 'Draft', label: 'Draft', color_class: 'text-slate-500' },
                    { id: 2, name: 'Published', label: 'Published', color_class: 'text-green-600' },
                    { id: 3, name: 'Scheduled', label: 'Scheduled', color_class: 'text-blue-600' },
                    { id: 4, name: 'Archived', label: 'Archived', color_class: 'text-rose-600' },
                ]
            };
        }
        return await get(route('articles.statuses.index'));
    }
};
