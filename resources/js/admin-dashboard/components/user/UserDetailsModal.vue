<script setup lang="ts">
/**
 * UserDetailsModal.vue
 * Detailed view modal for a specific user.
 */
import type { User } from '../../mockup/users';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';
import UserStatusBadge from './UserStatusBadge.vue';
import UserRoleBadge from './UserRoleBadge.vue';
import { useRouter } from 'vue-router';

const props = defineProps<{
    show: boolean;
    user: any; // Using any to match the dynamic table row payload
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const router = useRouter();

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const handleEdit = () => {
    emit('close');
    router.push({ name: 'users.edit', params: { id: props.user.id } });
};
</script>

<template>
    <BaseModal 
        :show="show" 
        :title="user ? `Member Profile` : 'User Details'" 
        @close="emit('close')"
    >
        <div v-if="user" class="overflow-hidden">
            <!-- Premium Profile Header -->
            <div class="relative mb-20">
                <!-- Cover/Banner Gradient -->
                <div class="h-32 w-full bg-gradient-to-r from-primary/80 to-primary rounded-2xl opacity-90"></div>
                
                <!-- Avatar Positioning (Overlapping) -->
                <div class="absolute -bottom-16 left-1/2 -translate-x-1/2">
                    <div class="h-32 w-32 rounded-3xl bg-white p-1.5 shadow-xl">
                        <div class="h-full w-full rounded-[1.2rem] bg-slate-100 flex items-center justify-center text-primary text-4xl font-bold overflow-hidden border border-slate-100 uppercase">
                            <img v-if="user.avatar" :src="'/storage/' + user.avatar" class="h-full w-full object-cover">
                            <template v-else>
                                {{ user.name.charAt(0) }}
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identity Section -->
            <div class="text-center mb-8">
                <h4 class="text-2xl font-black text-text-primary tracking-tight">{{ user.name }}</h4>
                <p class="text-text-secondary font-medium">{{ user.email }}</p>

                <!-- Badge Row -->
                <div class="flex justify-center items-center gap-3 mt-4">
                    <UserStatusBadge :status="user.status" />
                    <UserRoleBadge :label="user.roles && user.roles.length > 0 ? user.roles[0].name : 'No Role'" />
                </div>
            </div>

            <!-- Focused Information Sections -->
            <div class="space-y-6 px-2">
                <!-- Section: Account Identity -->
                <div>
                    <h5 class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 px-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary/40"></span>
                        Account Identity
                    </h5>
                    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-white rounded-lg shadow-sm border border-slate-100">
                                <span class="material-symbols-outlined text-slate-400 text-[20px]">fingerprint</span>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase">Unique ID</label>
                                <span class="text-sm font-bold text-text-primary">#{{ user.id }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-white rounded-lg shadow-sm border border-slate-100">
                                <span class="material-symbols-outlined text-slate-400 text-[20px]">alternate_email</span>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase">Contact Email</label>
                                <span class="text-sm font-bold text-text-primary">{{ user.email }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Presence Timestamps -->
                <div>
                    <h5 class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 px-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        System Timestamps
                    </h5>
                    <div class="bg-white border border-slate-100 rounded-3xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-emerald-500/60 text-[20px] mt-1">calendar_add_on</span>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase">Member Since</label>
                                <span class="text-sm font-semibold text-text-primary block leading-tight">{{ formatDate(user.created_at) }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-amber-500/60 text-[20px] mt-1">update</span>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase">Profile Integrity</label>
                                <span class="text-sm font-semibold text-text-primary block leading-tight">Modified {{ formatDate(user.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex gap-3 w-full sm:w-auto">
                <BaseButton variant="ghost" class="flex-1 sm:flex-none" @click="emit('close')">
                    Dismiss
                </BaseButton>
                <BaseButton icon="edit_square" class="flex-1 sm:flex-none" @click="handleEdit">
                    Modify Account
                </BaseButton>
            </div>
        </template>
    </BaseModal>
</template>
