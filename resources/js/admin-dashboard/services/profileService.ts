import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';

/**
 * profileService.ts
 * Dedicated service for user profile operations.
 */
export const profileService = {
    /**
     * Get the authenticated user's profile.
     */
    async show() {
        const { get } = useApi();
        return await get(route('profile.show'));
    },

    /**
     * Update the authenticated user's profile.
     * @param payload FormData containing name, email, avatar, etc.
     */
    async update(payload: FormData) {
        const { post } = useApi();
        return await post(route('profile.update'), payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });
    }
};
