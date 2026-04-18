<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';
import BaseSelect from '../ui/BaseSelect.vue';
import BaseTagsInput from '../ui/BaseTagsInput.vue';
import { useMediaMetadataOptions } from '../../composables/useMediaMetadataOptions';

const props = defineProps<{
    show: boolean;
    initialFilters?: {
        title: string;
        alt_text: string;
        caption: string;
        description: string;
        category_id: string;
        tags: string[];
    };
}>();

const emit = defineEmits<{
    (e: 'filter', filters: {
        title: string;
        alt_text: string;
        caption: string;
        description: string;
        category_id: string;
        tags: string[];
    }): void;
    (e: 'close'): void;
    (e: 'reset'): void;
}>();

const title = ref('');
const altText = ref('');
const caption = ref('');
const description = ref('');
const categoryId = ref('');
const tags = ref<string[]>([]);

const {
    categoryOptions,
    isLoadingCategories,
    hasImageCategoryGroup,
    tagSuggestions,
    fetchImageCategories,
    fetchTagSuggestions,
} = useMediaMetadataOptions();

const syncFromProps = () => {
    title.value = props.initialFilters?.title || '';
    altText.value = props.initialFilters?.alt_text || '';
    caption.value = props.initialFilters?.caption || '';
    description.value = props.initialFilters?.description || '';
    categoryId.value = props.initialFilters?.category_id || '';
    tags.value = props.initialFilters?.tags || [];
};

watch(() => props.show, (show) => {
    if (show) {
        syncFromProps();
    }
});

const handleFilter = () => {
    emit('filter', {
        title: title.value,
        alt_text: altText.value,
        caption: caption.value,
        description: description.value,
        category_id: categoryId.value,
        tags: tags.value,
    });
    emit('close');
};

const handleReset = () => {
    title.value = '';
    altText.value = '';
    caption.value = '';
    description.value = '';
    categoryId.value = '';
    tags.value = [];
    emit('reset');
    emit('close');
};

onMounted(() => {
    fetchImageCategories();
    fetchTagSuggestions();
});
</script>

<template>
    <BaseModal :show="show" title="Advanced Media Search" @close="emit('close')">
        <div class="space-y-6 py-2">
            <div class="grid grid-cols-1 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold uppercase tracking-wider text-text-secondary">Title</label>
                    <input
                        v-model="title"
                        type="text"
                        placeholder="Cari berdasarkan judul media..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold uppercase tracking-wider text-text-secondary">Alt Text</label>
                    <input
                        v-model="altText"
                        type="text"
                        placeholder="Cari berdasarkan alt text..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold uppercase tracking-wider text-text-secondary">Caption</label>
                    <input
                        v-model="caption"
                        type="text"
                        placeholder="Cari berdasarkan caption..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold uppercase tracking-wider text-text-secondary">Description</label>
                    <textarea
                        v-model="description"
                        rows="4"
                        placeholder="Cari berdasarkan deskripsi..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    ></textarea>
                </div>

                <BaseSelect
                    label="Category"
                    v-model="categoryId"
                    :options="categoryOptions"
                    :placeholder="isLoadingCategories ? 'Loading image categories...' : 'Filter by category'"
                />

                <p v-if="!hasImageCategoryGroup" class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-600">
                    Images category group was not found. Category filter is unavailable.
                </p>

                <BaseTagsInput
                    label="Tags"
                    v-model="tags"
                    :suggestions="tagSuggestions"
                    placeholder="Type tag filter and press Enter..."
                />
            </div>
        </div>

        <template #footer>
            <BaseButton variant="ghost" @click="handleReset">
                Reset All
            </BaseButton>
            <BaseButton icon="search" @click="handleFilter">
                Show Results
            </BaseButton>
        </template>
    </BaseModal>
</template>
