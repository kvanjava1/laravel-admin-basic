import { route } from 'ziggy-js';
import { useApi } from '../composables/useApi';

export interface MediaItem {
    id: number;
    category_id: number | null;
    title: string;
    alt_text: string;
    caption: string | null;
    original_url: string | null;
    thumbnail_url: string | null;
    output_mime_type: string;
    created_at: string;
    category?: { id: number; name: string; slug: string } | null;
    tags_preview: { id: number; name: string; slug: string }[];
}

export interface MediaVariant {
    key: string;
    label: string;
    path: string | null;
    url: string | null;
    size: number;
}

export interface MediaDetail extends MediaItem {
    uuid: string;
    created_by: number | null;
    slug: string;
    description: string | null;
    original_mime_type: string | null;
    original_size: number | null;
    total_variant_size: number;
    original_path: string;
    ratio_16_9_medium_path: string;
    ratio_16_9_big_path: string;
    ratio_4_3_medium_path: string;
    ratio_4_3_big_path: string;
    original_url: string | null;
    ratio_16_9_medium_url: string | null;
    ratio_16_9_big_url: string | null;
    ratio_4_3_medium_url: string | null;
    ratio_4_3_big_url: string | null;
    crop_16_9_x: number;
    crop_16_9_y: number;
    crop_16_9_width: number;
    crop_16_9_height: number;
    crop_4_3_x: number;
    crop_4_3_y: number;
    crop_4_3_width: number;
    crop_4_3_height: number;
    variants: MediaVariant[];
    tags: { id: number; name: string; slug: string }[];
    creator?: { id: number; name: string; email: string } | null;
    updated_at: string;
}

const { get, post, put, delete: destroy } = useApi();

export const mediaService = {
    async getPaginated(params: Record<string, any> = {}) {
        return await get(route('media.index'), { params });
    },

    async show(id: number) {
        return await get(route('media.show', id));
    },

    async destroy(id: number) {
        return await destroy(route('media.destroy', id));
    },

    async update(id: number, data: Record<string, any>) {
        return await put(route('media.update', id), data);
    },

    async store(data: Record<string, any>) {
        const formData = new FormData();

        Object.entries(data).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach((item) => {
                    if (item !== undefined && item !== null && item !== '') {
                        formData.append(`${key}[]`, String(item));
                    }
                });
                return;
            }

            if (value !== undefined && value !== null && value !== '') {
                formData.append(key, value as string | Blob);
            }
        });

        return await post(route('media.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
};
