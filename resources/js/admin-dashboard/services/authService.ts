import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';
import { useAuth } from '../composables/useAuth';

/**
 * authService.ts
 * Auth-related API calls using the useApi utility.
 */

export const authService = {
    /**
     * Login user
     */
    async login(credentials: any) {
        const { post } = useApi();
        const { setAuth } = useAuth();

        try {
            const data = await post(route('login'), credentials);

            if (data.success) {
                setAuth(data.data.user, data.data.token);
            }

            return data;
        } catch (error) {
            throw error;
        }
    },

    /**
     * Logout user
     */
    async logout() {
        const { post } = useApi();
        const { clearAuth } = useAuth();

        try {
            const data = await post(route('logout'));
            clearAuth();
            return data;
        } catch (error) {
            // Even if logout fails (e.g., token already revoked), clear local state
            clearAuth();
            throw error;
        }
    },

    /**
     * Get current user (me)
     */
    async me() {
        const { get } = useApi();
        const { setAuth } = useAuth();

        try {
            const data = await get(route('me'));
            if (data.success) {
                // Update current user data but keep same token
                const token = localStorage.getItem('auth_token') || '';
                setAuth(data.data, token);
            }
            return data;
        } catch (error) {
            throw error;
        }
    }
};
