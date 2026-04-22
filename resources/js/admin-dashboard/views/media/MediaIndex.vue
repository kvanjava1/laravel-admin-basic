<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useMediaList } from '../../composables/useMediaList';
import MediaFilterModal from '../../components/media/MediaFilterModal.vue';
import MediaDetailsModal from '../../components/media/MediaDetailsModal.vue';

import BasePanel from '../../components/ui/BasePanel.vue';
import TablePagination from '../../components/ui/TablePagination.vue';
import ActionMenu from '../../components/ui/ActionMenu.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import { alertService } from '../../utils/sweetalert';
import { mediaService, type MediaDetail } from '../../services/mediaService';

const router = useRouter();
const {
    mediaItems,
    isLoading,
    showAdvancedFilter,
    advancedFilters,
    pagination,
    fetchMedia,
    handleAdvancedFilter,
    clearFilters,
    handlePageChange
} = useMediaList();
const showDetailsModal = ref(false);
const selectedMedia = ref<any | null>(null);

const formatDate = (value: string) => value.slice(0, 10);

const getRowActions = (item: any) => [
    { label: 'Detail Media', icon: 'info', handler: () => openDetails(item) },
    { label: 'Edit Media', icon: 'edit', handler: () => router.push({ name: 'media.edit', params: { id: item.id } }) },
    { label: 'Delete Media', icon: 'delete', colorClass: 'text-rose-600', handler: () => handleDelete(item) },
];

const hasActiveAdvancedFilters = () => {
    return Boolean(
        advancedFilters.value.title ||
        advancedFilters.value.alt_text ||
        advancedFilters.value.caption ||
        advancedFilters.value.description ||
        advancedFilters.value.category_id ||
        advancedFilters.value.tags.length
    );
};

const headerActions = [
    {
        label: 'Advanced Search',
        icon: 'filter_list',
        handler: () => {
            showAdvancedFilter.value = true;
        },
    },
    {
        label: 'Clear Filters',
        icon: 'restart_alt',
        handler: () => {
            clearFilters();
        },
        hidden: () => !hasActiveAdvancedFilters(),
    },
];

const openDetails = (item: any) => {
    selectedMedia.value = item;
    showDetailsModal.value = true;
};

const closeDetails = () => {
    showDetailsModal.value = false;
    selectedMedia.value = null;
};

const handleDelete = async (item: any) => {
    const result = await alertService.confirm({
        title: 'Delete this media?',
        text: `Media "${item.title}" will be removed from the library list.`,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        danger: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        await mediaService.destroy(item.id);
        alertService.successToast(`Media "${item.title}" deleted successfully.`);
        closeDetails();
        fetchMedia(pagination.value.currentPage);
    } catch (error: any) {
        alertService.errorToast(
            error.response?.data?.message || 'Delete Failed',
            'Could not delete the media item.'
        );
    }
};
</script>

<template>
    <BasePageContainer variant="wide">
        <BasePageHeader title="Media Library" subtitle="Kelola hasil upload, review crop, dan pantau metadata media." />

        <BasePanel title="Browse Media" icon="imagesmode" stacked-header>
            <template #actions>
                <div class="flex w-full items-center justify-between">
                    <BaseButton icon="add_a_photo" size="md" @click="router.push({ name: 'media.create' })">
                        Upload Media
                    </BaseButton>

                    <ActionMenu
                        :actions="headerActions.filter(action => !action.hidden || !action.hidden())"
                        size="md"
                    />
                </div>
            </template>

            <template #top-content>
                <div
                    v-if="hasActiveAdvancedFilters()"
                    class="flex items-center justify-between rounded-2xl border border-primary/15 bg-primary/5 px-4 py-3"
                >
                    <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-primary/80">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                        <span>Advanced Search Active</span>
                    </div>
                    <button
                        class="text-[11px] font-black uppercase tracking-[0.14em] text-primary transition-colors hover:text-primary/70"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </button>
                </div>
            </template>

            <div class="px-1 py-2">
                <div v-if="isLoading" class="py-24 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                        <span class="material-symbols-outlined animate-pulse text-4xl">hourglass_top</span>
                    </div>
                    <p class="mt-4 text-base font-bold text-slate-500">Loading media library...</p>
                    <p class="mt-1 text-sm font-medium text-slate-400">Mengambil hasil upload dan metadata terbaru.</p>
                </div>

                <div v-else-if="mediaItems.length > 0" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="item in mediaItems"
                        :key="item.id"
                        class="cursor-pointer overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-colors hover:border-primary/35 hover:shadow-md"
                        @click="openDetails(item)"
                    >
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                            <img
                                :src="item.thumbnail_url || item.original_url || ''"
                                :alt="item.alt_text"
                                class="h-full w-full object-cover"
                            >

                            <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3">
                                <span class="rounded-full bg-slate-950/80 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white">
                                    {{ item.output_mime_type?.split('/')[1]?.toUpperCase() || 'IMG' }}
                                </span>
                                <div @click.stop>
                                    <ActionMenu :actions="getRowActions(item)" size="md" />
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-slate-200"></div>

                        <div class="space-y-4 p-4">
                            <div class="space-y-1.5">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                                    {{ formatDate(item.created_at) }}
                                </p>
                                <div v-if="item.category?.name || item.tags_preview?.length" class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="item.category?.name"
                                        class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-700"
                                    >
                                        {{ item.category.name }}
                                    </span>
                                    <span
                                        v-for="tag in item.tags_preview"
                                        :key="tag.id"
                                        class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-amber-700"
                                    >
                                        {{ tag.name }}
                                    </span>
                                </div>
                                <h3 class="line-clamp-2 text-base font-bold leading-tight text-slate-800" :title="item.title">
                                    {{ item.title }}
                                </h3>
                                <p class="line-clamp-2 min-h-[2.5rem] text-sm font-medium text-slate-500" :title="item.caption || item.alt_text">
                                    {{ item.caption || item.alt_text }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="py-20 text-center text-text-secondary">
                    <span class="material-symbols-outlined mb-2 block text-4xl">image_not_supported</span>
                    <p class="text-base font-semibold text-slate-600">
                        <template v-if="hasActiveAdvancedFilters()">
                            Tidak ada media yang cocok dengan filter saat ini.
                        </template>
                        <template v-else>
                            Belum ada media yang tampil di library.
                        </template>
                    </p>
                </div>
            </div>

            <template #footer>
                <TablePagination
                    :total="pagination.total"
                    :current-page="pagination.currentPage"
                    :last-page="pagination.lastPage"
                    :per-page="pagination.perPage"
                    :from="pagination.from"
                    :to="pagination.to"
                    @on-change="handlePageChange"
                />
            </template>
        </BasePanel>

        <MediaFilterModal
            :show="showAdvancedFilter"
            :initial-filters="advancedFilters"
            @close="showAdvancedFilter = false"
            @filter="handleAdvancedFilter"
            @reset="clearFilters"
        />

        <MediaDetailsModal
            :show="showDetailsModal"
            :media="selectedMedia"
            @close="closeDetails"
        />
    </BasePageContainer>
</template>
