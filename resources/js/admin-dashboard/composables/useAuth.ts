import { ref, computed } from 'vue';

/**
 * useAuth.ts
 * Manages global authentication state.
 */

// Global singleton state
const user = ref<any>(JSON.parse(localStorage.getItem('user_data') || 'null'));
const token = ref<string | null>(localStorage.getItem('auth_token'));

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value);

    const setAuth = (userData: any, authToken: string) => {
        user.value = userData;
        token.value = authToken;
        localStorage.setItem('user_data', JSON.stringify(userData));
        localStorage.setItem('auth_token', authToken);
    };

    const clearAuth = () => {
        user.value = null;
        token.value = null;
        localStorage.removeItem('user_data');
        localStorage.removeItem('auth_token');
    };

    return {
        user,
        token,
        isAuthenticated,
        setAuth,
        clearAuth,
    };
}
