import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios';
import { ref } from 'vue';

/**
 * useApi.ts
 * A composable for making authenticated API requests.
 */

const isLoading = ref(false);
const error = ref<{ message: string; description?: string } | null>(null);

// Create a singleton axios instance for the dashboard
const api: AxiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }
});

// Request Interceptor to inject Bearer Token
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Response Interceptor for global error handling (e.g., redirect on 401)
api.interceptors.response.use(
    (response) => response,
    (err) => {
        if (err.response && err.response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            window.location.href = '/admin/login';
        }
        return Promise.reject(err);
    }
);

export function useApi() {
    const request = async <T = any>(config: AxiosRequestConfig): Promise<T> => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await api.request<T>(config);
            return response.data;
        } catch (err: any) {
            const errorMessage = err.response?.data?.message || 'An unexpected error occurred';
            const errorDescription = err.response?.data?.errors
                ? Object.values(err.response.data.errors).flat().join(' ')
                : '';

            error.value = {
                message: errorMessage,
                description: errorDescription,
            };
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        isLoading,
        error,
        get: <T = any>(url: string, config?: AxiosRequestConfig) => request<T>({ ...config, method: 'GET', url }),
        post: <T = any>(url: string, data?: any, config?: AxiosRequestConfig) => request<T>({ ...config, method: 'POST', url, data }),
        put: <T = any>(url: string, data?: any, config?: AxiosRequestConfig) => request<T>({ ...config, method: 'PUT', url, data }),
        patch: <T = any>(url: string, data?: any, config?: AxiosRequestConfig) => request<T>({ ...config, method: 'PATCH', url, data }),
        delete: <T = any>(url: string, config?: AxiosRequestConfig) => request<T>({ ...config, method: 'DELETE', url }),
    };
}
