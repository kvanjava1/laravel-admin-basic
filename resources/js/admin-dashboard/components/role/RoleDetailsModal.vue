<script setup lang="ts">
/**
 * RoleDetailsModal.vue
 * Detailed view modal for a specific role.
 */
import type { Role } from '../../services/roleService';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';
import UserRoleBadge from '../user/UserRoleBadge.vue';

const props = defineProps<{
    show: boolean;
    role: Role | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <BaseModal :show="show" :title="role ? `Role Detail: ${role.name}` : 'Role Details'" @close="emit('close')">
        <div v-if="role" class="space-y-8">
            <!-- Role Header -->
            <div class="flex items-center gap-6 p-4 rounded-3xl bg-slate-50 border border-slate-100">
                <div
                    class="h-20 w-20 rounded-2xl bg-primary/10 flex items-center justify-center text-primary text-3xl font-bold shadow-inner border border-slate-200">
                    <span class="material-symbols-outlined text-4xl">shield_person</span>
                </div>
                <div class="flex flex-col gap-1">
                    <h4 class="text-xl font-bold text-text-primary">{{ role.name }}</h4>
                    <p class="text-text-secondary text-sm font-medium">System Role</p>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Role ID -->
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Role ID</label>
                    <div class="flex items-center gap-2 text-text-primary font-semibold">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">fingerprint</span>
                        #{{ role.id }}
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Role Name</label>
                    <div class="flex items-center">
                        <UserRoleBadge :label="role.name" />
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Created Date</label>
                    <div class="flex items-center gap-2 text-text-primary font-semibold">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">calendar_today</span>
                        {{ formatDate(role.created_at) }}
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Last Updated</label>
                    <div class="flex items-center gap-2 text-text-primary font-semibold">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">history</span>
                        {{ formatDate(role.updated_at) }}
                    </div>
                </div>
            </div>

            <!-- Permissions Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">vpn_key</span>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Permissions</label>
                </div>
                <div class="flex flex-wrap gap-2">
                    <UserRoleBadge v-for="permission in role.permissions" :key="permission.id"
                        :label="permission.display_name || permission.name" icon="key" />
                    <span v-if="!role.permissions?.length" class="text-sm text-slate-400 italic">
                        No permissions assigned
                    </span>
                </div>
            </div>
        </div>

        <template #footer>
            <BaseButton variant="white" @click="emit('close')">
                Close
            </BaseButton>
        </template>
    </BaseModal>
</template>
