import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';

export interface TagOption {
    id: number;
    name: string;
    slug: string;
}

export const tagService = {
    async getOptions(params: Record<string, any> = {}) {
        const { get } = useApi();
        return await get(route('tags.options'), { params });
    },
};
