import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';

/**
 * userService.ts
 * Service layer for User-related API calls using useApi.
 */

const { get, post, delete: destroy } = useApi();

export const userService = {
    /**
     * Store a new user
     */
    async store(data: any) {
        const formData = new FormData();

        Object.keys(data).forEach(key => {
            if (data[key] !== undefined && data[key] !== null) {
                formData.append(key, data[key]);
            }
        });

        return await post(route('users.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
    },

    /**
     * Get paginated users
     */
    async getPaginated(params: any = {}) {
        return await get(route('users.index'), { params });
    },

    /**
     * Get all possible user statuses
     */
    async getStatuses() {
        return await get(route('users.statuses.index'));
    },

    /**
     * Generic method for fetching users
     */
    async getAll() {
        return await get(route('users.index'));
    },

    /**
     * Show single user
     */
    async show(id: number) {
        return await get(route('users.show', id));
    },

    /**
     * Update an existing user
     */
    async update(id: number, data: any) {
        const formData = new FormData();
        formData.append('_method', 'PUT');

        Object.keys(data).forEach(key => {
            if ((key === 'password' || key === 'password_confirmation') && !data[key]) {
                return;
            }

            if (key === 'avatar' && data[key] && !(data[key] instanceof File)) {
                return;
            }

            if (data[key] !== undefined && data[key] !== null) {
                formData.append(key, data[key]);
            }
        });

        return await post(route('users.update', id), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
    },

    /**
     * Delete a user
     */
    async destroy(id: number) {
        return await destroy(route('users.destroy', id));
    },

    /**
     * Get ban history for a user
     */
    async getBanHistory(id: number) {
        return await get(route('users.ban-history', id));
    },

    /**
     * Ban a user
     */
    async ban(id: number, data: { type: string, reason: string, expired_at?: string }) {
        return await post(route('users.ban', id), data);
    },

    /**
     * Unban/Restore a user
     */
    async unban(id: number, data: { reason: string }) {
        return await post(route('users.unban', id), data);
    },

    /**
     * Deactivate a user
     */
    async deactivate(id: number, data: { reason: string }) {
        return await post(route('users.deactivate', id), data);
    },

    /**
     * Activate a user
     */
    async activate(id: number, data: { reason: string }) {
        return await post(route('users.activate', id), data);
    }
};
