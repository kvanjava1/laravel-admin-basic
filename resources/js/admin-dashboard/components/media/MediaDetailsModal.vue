<script setup lang="ts">
import type { MediaDetail } from '../../services/mediaService';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';

const props = defineProps<{
    show: boolean;
    media: MediaDetail | null;
    isLoading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const formatDate = (value?: string | null) => {
    if (!value) return '-';

    return new Date(value).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatSize = (bytes?: number | null) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const openVariant = (url: string | null) => {
    if (url) {
        window.open(url, '_blank');
    }
};

const getVariantFormat = (_key: string, media?: MediaDetail | null) => {
    const mimeType = media?.output_mime_type || 'image/webp';
    const format = mimeType.split('/')[1] || mimeType;

    return format.toUpperCase();
};
</script>

<template>
    <BaseModal :show="show" :title="media ? media.title : 'Media Detail'" @close="emit('close')">
        <div v-if="isLoading" class="py-16 text-center">
            <span class="material-symbols-outlined animate-spin text-4xl text-primary">sync</span>
            <p class="mt-3 text-sm font-medium text-slate-500">Loading media details...</p>
        </div>

        <div v-else-if="media" class="space-y-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50">
                <div class="aspect-video overflow-hidden bg-slate-100">
                    <img :src="media.ratio_16_9_big_url || media.thumbnail_url || media.original_url || ''" :alt="media.alt_text" class="h-full w-full object-cover">
                </div>
                <div class="space-y-4 border-t border-slate-200 p-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Title</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ media.title }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Category</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ media.category?.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Caption</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ media.caption || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Tags</p>
                        <div v-if="media.tags.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="tag in media.tags"
                                :key="tag.id"
                                class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                        <p v-else class="mt-1 text-sm font-bold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Total Variant Size</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ formatSize(media.total_variant_size) }}</p>
                    </div>
                </div>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-5">
                <h4 class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">Image Inputs</h4>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Slug</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ media.slug }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">UUID</p>
                        <p class="mt-1 break-all text-sm font-semibold text-slate-700">{{ media.uuid }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Alt Text</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ media.alt_text }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Description</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ media.description || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Creator</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ media.creator?.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Original Mime</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ media.original_mime_type || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Original Size</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ formatSize(media.original_size) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Output Mime</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ media.output_mime_type }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Created</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ formatDate(media.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Updated</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ formatDate(media.updated_at) }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5">
                <h4 class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">Crop Coordinates</h4>
                <div class="mt-4 space-y-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">16:9 Crop</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">x: {{ media.crop_16_9_x }}, y: {{ media.crop_16_9_y }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">w: {{ media.crop_16_9_width }}, h: {{ media.crop_16_9_height }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">4:3 Crop</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">x: {{ media.crop_4_3_x }}, y: {{ media.crop_4_3_y }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">w: {{ media.crop_4_3_width }}, h: {{ media.crop_4_3_height }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5">
                <div class="space-y-3">
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">Variants</h4>
                        <p class="mt-1 text-sm font-medium text-slate-500">Klik link gambar untuk membuka variant terkait.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Total Variant Size</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ formatSize(media.total_variant_size) }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 md:hidden">
                    <div
                        v-for="variant in media.variants"
                        :key="`${variant.key}-mobile`"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Variant</p>
                                <p class="mt-1 text-base font-bold text-slate-800">{{ variant.label }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Format</p>
                                <p class="mt-1 text-sm font-medium text-slate-600">{{ getVariantFormat(variant.key, media) }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Size</p>
                                <p class="mt-1 text-sm font-bold text-slate-800">{{ formatSize(variant.size) }}</p>
                            </div>

                            <BaseButton variant="ghost" size="sm" icon="open_in_new" class="w-full justify-center" @click="openVariant(variant.url)">
                                Open Variant
                            </BaseButton>
                        </div>
                    </div>
                </div>

                <div class="mt-4 hidden overflow-hidden rounded-2xl border border-slate-200 md:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Variant</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Format</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Size</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="variant in media.variants" :key="variant.key" class="align-top">
                                    <td class="px-4 py-4">
                                        <p class="text-sm font-bold text-slate-800">{{ variant.label }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="text-xs font-medium text-slate-500">{{ getVariantFormat(variant.key, media) }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="text-sm font-bold text-slate-700">{{ formatSize(variant.size) }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <BaseButton variant="ghost" size="sm" icon="open_in_new" @click="openVariant(variant.url)">
                                            Detail
                                        </BaseButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <template #footer>
            <BaseButton variant="ghost" @click="emit('close')">
                Close
            </BaseButton>
        </template>
    </BaseModal>
</template>
