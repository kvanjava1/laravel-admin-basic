<script setup lang="ts">
import { ref, onMounted } from 'vue';
import BaseModal from '../ui/BaseModal.vue';
import BaseInput from '../ui/BaseInput.vue';
import BaseSelect from '../ui/BaseSelect.vue';
import BaseButton from '../ui/BaseButton.vue';
import { articleService } from '../../services/articleService';

interface Props {
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'filter', 'reset']);

const filters = ref({
    search: '',
    status: '',
    category: '',
    author: '',
    published_from: '',
    published_to: ''
});

const statusOptions = ref<any[]>([]);
const isLoading = ref(false);

onMounted(async () => {
    try {
        const response = await articleService.getStatuses();
        statusOptions.value = [
            { label: 'All Status', value: '' },
            ...response.data.map((s: any) => ({ label: s.label, value: s.name }))
        ];
    } catch (err) {
        console.error('Failed to load statuses', err);
    }
});

const handleApply = () => {
    emit('filter', { ...filters.value });
    emit('close');
};

const handleReset = () => {
    filters.value = {
        search: '',
        status: '',
        category: '',
        author: '',
        published_from: '',
        published_to: ''
    };
    emit('reset');
    emit('close');
};
</script>

<template>
    <BaseModal :show="show" title="Advanced Search: Articles" icon="manage_search" @close="emit('close')" max-width="2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-1">
            <div class="md:col-span-2">
                <BaseInput label="Search Keyword" icon="search" v-model="filters.search"
                    placeholder="Search in title or content..." />
            </div>

            <BaseSelect label="Publication Status" v-model="filters.status" :options="statusOptions" />
            
            <BaseInput label="Author" icon="person" v-model="filters.author" placeholder="Writer name" />

            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 ml-1 mb-2">Publication Date Range</label>
                <div class="grid grid-cols-2 gap-4">
                    <BaseInput label="From" icon="calendar_today" type="date" v-model="filters.published_from" />
                    <BaseInput label="To" icon="event_available" type="date" v-model="filters.published_to" />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between w-full">
                <BaseButton variant="ghost" @click="handleReset">Reset All</BaseButton>
                <div class="flex gap-3">
                    <BaseButton variant="ghost" @click="emit('close')">Cancel</BaseButton>
                    <BaseButton icon="filter_alt" @click="handleApply">Apply Filters</BaseButton>
                </div>
            </div>
        </template>
    </BaseModal>
</template>
