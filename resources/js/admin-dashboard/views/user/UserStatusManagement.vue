<script setup lang="ts">
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useUserStatus } from '../../composables/useUserStatus';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import UserStatusBadge from '../../components/user/UserStatusBadge.vue';
import { format } from 'date-fns';

const route = useRoute();
const userId = Number(route.params.id);

const {
    user,
    isLoading,
    isSubmitting,
    banType,
    banReason,
    deactivateReason,
    activateReason,
    expiredAt,
    unbanReason,
    sortedHistory,
    fetchHistory,
    handleBan,
    handleUnban,
    handleDeactivate,
    handleActivate
} = useUserStatus(userId);

const getActionConfig = (action: string) => {
    switch(action) {
        case 'banned': return { label: 'Banned', icon: 'block', color: 'bg-red-50 text-red-600' };
        case 'restored': return { label: 'Restored', icon: 'check_circle', color: 'bg-emerald-50 text-emerald-600' };
        case 'activated': return { label: 'Activated', icon: 'play_circle', color: 'bg-blue-50 text-blue-600' };
        case 'deactivated': return { label: 'Deactivated', icon: 'pause_circle', color: 'bg-slate-50 text-slate-600' };
        default: return { label: action, icon: 'info', color: 'bg-slate-50 text-slate-600' };
    }
};

onMounted(fetchHistory);
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader 
            title="Account Status" 
            subtitle="Manage user status and history"
            backLabel="Back to Users" 
            backRouteName="users.index" 
        />

        <div v-if="isLoading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
        </div>

        <div v-else-if="user" class="space-y-6">
            <!-- 1. Target User Summary -->
            <div class="light-panel rounded-xl p-6 border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200">
                    <img v-if="user.avatar" :src="'/storage/' + user.avatar" class="h-full w-full object-cover" />
                    <span v-else class="material-symbols-outlined text-slate-400 text-3xl">person</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">{{ user.name }}</h3>
                    <p class="text-slate-500 text-sm mb-1">{{ user.email }}</p>
                    <UserStatusBadge :status="user.status?.name" />
                </div>
            </div>

            <!-- 2. Governance Controls -->
            
            <!-- Warning for Protected Accounts -->
            <div v-if="user.is_protected" class="bg-amber-50 border border-amber-200 rounded-xl p-6 flex flex-col md:flex-row items-center gap-4 animate-in fade-in slide-in-from-top duration-500">
                <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-amber-600">shield_person</span>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h4 class="font-bold text-amber-900 leading-none mb-1">System Protected Account</h4>
                    <p class="text-amber-700 text-sm">
                        This user is identified as a protected system account (e.g. Super Administrator). 
                        Direct status modifications like <span class="font-bold underline">Deactivation</span> or <span class="font-bold underline">Banning</span> are disabled to prevent locking out essential administrative access.
                    </p>
                </div>
            </div>

            <!-- CASE 1: User is Banned -> Show Restore -->
            <BasePanel v-if="user.status?.name === 'Banned'" title="Restore Account" icon="settings_backup_restore">
                <div class="space-y-4">
                    <p class="text-sm text-slate-600 italic border-l-4 border-primary/30 pl-4 py-1">
                        Restoring a banned account should be documented with a clear justification.
                    </p>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1">Justification for Restore</label>
                        <textarea 
                            v-model="unbanReason"
                            placeholder="Explain why this account is being restored..."
                            class="w-full min-h-[100px] rounded-xl border-slate-200 focus:border-primary/40 focus:ring-primary/20 text-sm p-3 transition-all"
                        ></textarea>
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end p-4 bg-slate-50 rounded-xl">
                        <BaseButton icon="check_circle" @click="handleUnban" :loading="isSubmitting" :disabled="!unbanReason">
                            Restore Account
                        </BaseButton>
                    </div>
                </template>
            </BasePanel>

            <!-- CASE 2: User is Active -> Show Deactivate & Ban (If not protected) -->
            <template v-else-if="user.status?.name === 'Active' && !user.is_protected">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Deactivate Section -->
                    <BasePanel title="Deactivate Account" icon="person_off">
                        <div class="space-y-4">
                            <p class="text-sm text-slate-500">Temporarily disable access without a formal ban.</p>
                            <BaseInput label="Reason for Deactivation" v-model="deactivateReason" placeholder="e.g. User request, maintenance..." />
                        </div>
                        <template #footer>
                            <div class="flex justify-end p-4 bg-slate-50 rounded-xl">
                                <BaseButton variant="ghost" icon="pause_circle" @click="handleDeactivate" :loading="isSubmitting" :disabled="!deactivateReason">
                                    Deactivate
                                </BaseButton>
                            </div>
                        </template>
                    </BasePanel>

                    <!-- Ban Section (Simplified here for layout) -->
                    <BasePanel title="Ban Account" icon="block">
                        <div class="space-y-3">
                            <div class="flex gap-2">
                                <button @click="banType = 'permanent'" 
                                    :class="[banType === 'permanent' ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-200 scale-[1.02]' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-white hover:border-slate-300', 'flex-1 py-2.5 px-4 rounded-xl text-[11px] font-black uppercase tracking-wider border transition-all flex items-center justify-center gap-2']">
                                    <span class="material-symbols-outlined text-[18px]" v-if="banType === 'permanent'">emergency_home</span>
                                    <span>Permanent</span>
                                </button>
                                <button @click="banType = 'temporary'" 
                                    :class="[banType === 'temporary' ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200 scale-[1.02]' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-white hover:border-slate-300', 'flex-1 py-2.5 px-4 rounded-xl text-[11px] font-black uppercase tracking-wider border transition-all flex items-center justify-center gap-2']">
                                    <span class="material-symbols-outlined text-[18px]" v-if="banType === 'temporary'">timer</span>
                                    <span>Temporary</span>
                                </button>
                            </div>
                            <BaseInput v-if="banType === 'temporary'" label="Expires At" type="datetime-local" v-model="expiredAt" size="sm" />
                            <BaseInput label="Reason" v-model="banReason" placeholder="Violations, TOS..." size="sm" />
                        </div>
                        <template #footer>
                            <div class="flex justify-end p-4 bg-slate-50 rounded-xl">
                                <BaseButton variant="danger" icon="gavel" @click="handleBan" :loading="isSubmitting" :disabled="!banReason">
                                    Execute Ban
                                </BaseButton>
                            </div>
                        </template>
                    </BasePanel>
                </div>
            </template>

            <!-- CASE 3: User is Inactive -> Show Activate & Ban (If not protected) -->
            <template v-else-if="user.status?.name === 'Inactive' && !user.is_protected">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Activate Section -->
                    <BasePanel title="Activate Account" icon="person_check">
                        <div class="space-y-4">
                            <p class="text-sm text-slate-500">Restore normal access to the system.</p>
                            <BaseInput label="Reason for Activation" v-model="activateReason" placeholder="e.g. Account verified, issue resolved..." />
                        </div>
                        <template #footer>
                            <div class="flex justify-end p-4 bg-slate-50 rounded-xl">
                                <BaseButton icon="play_circle" @click="handleActivate" :loading="isSubmitting" :disabled="!activateReason">
                                    Activate
                                </BaseButton>
                            </div>
                        </template>
                    </BasePanel>

                    <!-- Ban Section (Disciplinary) -->
                    <BasePanel title="Ban Account (Disciplinary)" icon="block">
                         <div class="space-y-3">
                            <div class="flex gap-2">
                                <button @click="banType = 'permanent'" 
                                    :class="[banType === 'permanent' ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-200 scale-[1.02]' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-white hover:border-slate-300', 'flex-1 py-2.5 px-4 rounded-xl text-[11px] font-black uppercase tracking-wider border transition-all flex items-center justify-center gap-2']">
                                    <span class="material-symbols-outlined text-[18px]" v-if="banType === 'permanent'">emergency_home</span>
                                    <span>Permanent</span>
                                </button>
                                <button @click="banType = 'temporary'" 
                                    :class="[banType === 'temporary' ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200 scale-[1.02]' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-white hover:border-slate-300', 'flex-1 py-2.5 px-4 rounded-xl text-[11px] font-black uppercase tracking-wider border transition-all flex items-center justify-center gap-2']">
                                    <span class="material-symbols-outlined text-[18px]" v-if="banType === 'temporary'">timer</span>
                                    <span>Temporary</span>
                                </button>
                            </div>
                            <BaseInput v-if="banType === 'temporary'" label="Expires At" type="datetime-local" v-model="expiredAt" size="sm" />
                            <BaseInput label="Reason" v-model="banReason" placeholder="Violations, TOS..." size="sm" />
                        </div>
                        <template #footer>
                            <div class="flex justify-end p-4 bg-slate-50 rounded-xl">
                                <BaseButton variant="danger" icon="gavel" @click="handleBan" :loading="isSubmitting" :disabled="!banReason">
                                    Execute Ban
                                </BaseButton>
                            </div>
                        </template>
                    </BasePanel>
                </div>
            </template>

            <!-- 3. Audit Trail -->
            <BasePanel title="Ban History" icon="history">
                <div v-if="sortedHistory.length === 0" class="py-12 text-center">
                    <span class="material-symbols-outlined text-slate-300 text-5xl mb-2">event_busy</span>
                    <p class="text-slate-400 text-sm font-medium">No actions recorded yet.</p>
                </div>
                
                <div v-else class="overflow-hidden">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <li v-for="(item, idx) in sortedHistory" :key="item.id">
                                <div class="relative pb-8">
                                    <span v-if="idx !== sortedHistory.length - 1" class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-slate-100" aria-hidden="true"></span>
                                    <div class="relative flex items-start space-x-3">
                                        <div :class="[
                                            'relative h-10 w-10 rounded-full flex items-center justify-center ring-8 ring-white shadow-sm border border-white/50',
                                            getActionConfig(item.action).color
                                        ]">
                                            <span class="material-symbols-outlined text-[20px]">
                                                {{ getActionConfig(item.action).icon }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 py-1.5">
                                            <div class="text-sm flex items-center justify-between">
                                                <p class="font-bold text-slate-900">
                                                    {{ getActionConfig(item.action).label }}
                                                    <span class="text-slate-400 font-normal mx-1">by</span>
                                                    {{ item.admin?.name || 'System' }}
                                                </p>
                                                <time class="text-slate-400 text-xs tabular-nums">
                                                    {{ format(new Date(item.created_at), 'MMM d, yyyy HH:mm') }}
                                                </time>
                                            </div>
                                            <div class="mt-1 text-sm text-slate-600">
                                                <p class="bg-slate-50 p-3 rounded-xl border border-slate-100 italic">
                                                    "{{ item.reason }}"
                                                </p>
                                            </div>
                                            <div v-if="item.action === 'banned' && item.type === 'temporary'" class="mt-2 flex items-center gap-1.5 text-xs font-bold uppercase tracking-tight text-orange-600 bg-orange-50 px-2 py-1 rounded-md w-fit border border-orange-100">
                                                <span class="material-symbols-outlined text-[14px]">timer</span>
                                                Expires: {{ format(new Date(item.expired_at), 'MMM d, yyyy HH:mm') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </BasePanel>
        </div>
    </BasePageContainer>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: both;
}
</style>
