<script setup lang="ts">
/**
 * RoleFilterModal.vue
 * Advanced filtering modal for the Role Management page.
 */
import { ref, onMounted } from 'vue';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';
import { roleService, type Permission } from '../../services/roleService';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'filter', filters: { 
        search: string; 
        permissions: number[];
        created_at_from: string;
        created_at_to: string;
        updated_at_from: string;
        updated_at_to: string;
    }): void;
    (e: 'close'): void;
    (e: 'reset'): void;
}>();

const search = ref('');
const selectedPermissions = ref<number[]>([]);
const groupedPermissions = ref<Record<string, Permission[]>>({});
const isLoading = ref(false);

const createdAtFrom = ref('');
const createdAtTo = ref('');
const updatedAtFrom = ref('');
const updatedAtTo = ref('');

const fetchPermissions = async () => {
    isLoading.value = true;
    try {
        const response = await roleService.getPermissions();
        // The API returns grouped permissions { groupName: Permission[] }
        groupedPermissions.value = response.data;
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchPermissions();
});

const handleSearch = () => {
    emit('filter', {
        search: search.value,
        permissions: selectedPermissions.value,
        created_at_from: createdAtFrom.value,
        created_at_to: createdAtTo.value,
        updated_at_from: updatedAtFrom.value,
        updated_at_to: updatedAtTo.value
    });
    emit('close');
};

const handleReset = () => {
    search.value = '';
    selectedPermissions.value = [];
    createdAtFrom.value = '';
    createdAtTo.value = '';
    updatedAtFrom.value = '';
    updatedAtTo.value = '';
    emit('reset');
};

const togglePermission = (id: number) => {
    const index = selectedPermissions.value.indexOf(id);
    if (index === -1) {
        selectedPermissions.value.push(id);
    } else {
        selectedPermissions.value.splice(index, 1);
    }
};
</script>

<template>
    <BaseModal 
        :show="show" 
        title="Show Filter" 
        @close="emit('close')"
    >
        <div class="space-y-6 py-2">
            <!-- Role Name -->
            <div class="flex flex-col gap-2">
                <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">Role Name</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary/50 text-[20px]">shield</span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search by role name..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    >
                </div>
            </div>

            <!-- Date Range Filters -->
            <div class="space-y-6">
                <!-- Created At Group -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider block">Created At</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">From</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">calendar_today</span>
                                <input v-model="createdAtFrom" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">To</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">calendar_today</span>
                                <input v-model="createdAtTo" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Updated At Group -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider block">Updated At</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">From</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">update</span>
                                <input v-model="updatedAtFrom" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">To</span>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">update</span>
                                <input v-model="updatedAtTo" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Checklist -->
            <div class="flex flex-col gap-3">
                <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">Filter by Permissions</label>
                
                <div v-if="isLoading" class="flex justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>

                <div v-else class="max-h-[400px] overflow-y-auto pr-2 space-y-6 custom-scrollbar">
                    <div v-for="(groupPermissions, groupName) in groupedPermissions" :key="groupName" class="space-y-4">
                        <div class="flex items-center gap-2 border-b-2 border-slate-200 pb-2">
                            <span class="material-symbols-outlined text-slate-500 text-sm">folder_open</span>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ groupName }}</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-2">
                            <div 
                                v-for="permission in groupPermissions" 
                                :key="permission.id"
                                @click="togglePermission(permission.id)"
                                class="group flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer select-none"
                                :class="selectedPermissions.includes(permission.id) 
                                    ? 'bg-primary/5 border-primary shadow-sm' 
                                    : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                            >
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="h-5 w-5 rounded border-2 flex items-center justify-center transition-all"
                                        :class="selectedPermissions.includes(permission.id)
                                            ? 'bg-primary border-primary text-white shadow-sm'
                                            : 'bg-white border-slate-400 group-hover:border-primary/50 text-transparent'"
                                    >
                                        <span class="material-symbols-outlined text-[14px] font-bold">check</span>
                                    </div>
                                    <span class="text-sm font-semibold transition-colors" :class="selectedPermissions.includes(permission.id) ? 'text-primary font-bold' : 'text-text-primary'">
                                        {{ permission.display_name || permission.name }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-300 font-mono opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ permission.name }}
                                </span>
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

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
