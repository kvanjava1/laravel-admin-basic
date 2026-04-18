<script setup lang="ts">
/**
 * UserFilterModal.vue
 * Advanced filtering modal for the User Management page.
 */
import { ref } from 'vue';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'filter', filters: { 
        search: string; 
        status: string; 
        created_from: string; 
        created_to: string; 
        updated_from: string; 
        updated_to: string; 
    }): void;
    (e: 'close'): void;
    (e: 'reset'): void;
}>();

const search = ref('');
const status = ref('');
const created_from = ref('');
const created_to = ref('');
const updated_from = ref('');
const updated_to = ref('');

const statuses = ['Any Status', 'Active', 'Pending', 'Banned', 'Inactive'];

const handleSearch = () => {
    emit('filter', {
        search: search.value,
        status: status.value,
        created_from: created_from.value,
        created_to: created_to.value,
        updated_from: updated_from.value,
        updated_to: updated_to.value
    });
    emit('close');
};

const handleReset = () => {
    search.value = '';
    status.value = '';
    created_from.value = '';
    created_to.value = '';
    updated_from.value = '';
    updated_to.value = '';
    emit('reset');
};
</script>

<template>
    <BaseModal 
        :show="show" 
        title="Advanced User Filter" 
        @close="emit('close')"
    >
        <div class="space-y-6 py-2">
            <!-- Search Input -->
            <div class="flex flex-col gap-2">
                <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">Search Member</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary/50 text-[20px]">search</span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search by name or email..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1">
                <!-- Status Select -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">Account Status</label>
                    <div class="relative">
                        <select 
                            v-model="status"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 pr-10 text-base appearance-none bg-none focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer"
                        >
                            <option value="">Any Status</option>
                            <option v-for="s in statuses.slice(1)" :key="s" :value="s">{{ s }}</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>

            <!-- Date Ranges -->
            <div class="grid grid-cols-1 gap-6 border-t border-slate-100 pt-6">
                <!-- Created At Range -->
                <div class="flex flex-col gap-3">
                    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">Created At</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">From</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">calendar_today</span>
                                <input type="date" v-model="created_from" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-600">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">To</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">calendar_today</span>
                                <input type="date" v-model="created_to" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-600">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Updated At Range -->
                <div class="flex flex-col gap-3">
                    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">Updated At</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">From</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">update</span>
                                <input type="date" v-model="updated_from" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-600">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">To</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">update</span>
                                <input type="date" v-model="updated_to" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-600">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <BaseButton variant="ghost" @click="handleReset">
                Reset All
            </BaseButton>
            <BaseButton icon="filter_list" @click="handleSearch">
                Show Results
            </BaseButton>
        </template>
    </BaseModal>
</template>
