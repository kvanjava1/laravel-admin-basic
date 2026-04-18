/**
 * roleService.ts
 * Service to handle role-related API calls using useApi.
 */
import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    group: string;
    display_name: string;
    created_at: string;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
}

const { get, post, put, delete: destroy } = useApi();

export const roleService = {
    /**
     * Get paginated roles
     */
    index: async (params: any = {}) => {
        return await get(route('roles.index'), { params });
    },

    /**
     * Get role options for dropdowns (id, name only)
     */
    getOptions: async () => {
        return await get(route('roles.options'));
    },

    /**
     * Get a single role
     */
    show: async (id: number) => {
        return await get(route('roles.show', id));
    },

    /**
     * Store a new role
     */
    store: async (data: any) => {
        return await post(route('roles.store'), data);
    },

    /**
     * Update an existing role
     */
    update: async (id: number, data: any) => {
        return await put(route('roles.update', id), data);
    },

    /**
     * Delete a role
     */
    destroy: async (id: number) => {
        return await destroy(route('roles.destroy', id));
    },

    /**
     * Get all available permissions
     */
    getPermissions: async () => {
        return await get(route('permissions.index'));
    }
};
