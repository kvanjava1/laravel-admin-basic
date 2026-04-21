<script setup lang="ts">
/**
 * RoleCreate.vue
 * Page for creating a new role.
 */
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useRoleForm } from '../../composables/useRoleForm';

// UI Components
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';

const router = useRouter();

const {
    name,
    permissions,
    availablePermissions,
    isLoading,
    validationErrors,
    submit,
    fetchPermissions,
    togglePermission
} = useRoleForm();

onMounted(() => {
    fetchPermissions();
});

const handleCancel = () => {
    router.push({ name: 'roles.index' });
};
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Create New Role" subtitle="Define permissions for specific system access"
            back-label="Back to Roles" back-route-name="roles.index" />

        <!-- Main Form (Single Column) -->
        <div class="form-content-wrapper space-y-6">
            <!-- Basic Info -->
            <BasePanel title="Role Information">
                <div class="space-y-6 py-2">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <BaseInput label="Role Name" icon="shield" v-model="name" placeholder="e.g. Content Manager"
                            autofocus :error="validationErrors.name?.[0]" />

                        <div
                            class="p-4 bg-slate-50 rounded-xl border border-slate-100 italic text-xs text-slate-500 leading-relaxed h-full flex items-center">
                            <div>
                                <span
                                    class="material-symbols-outlined text-[16px] align-middle mr-1 text-primary">info</span>
                                Roles define what actions your users can perform. Be specific with the naming to avoid
                                confusion.
                            </div>
                        </div>
                    </div>
                </div>
            </BasePanel>

            <!-- Permissions Selection -->
            <BasePanel title="Permissions Management">
                <div class="space-y-8 py-2">
                    <div v-if="validationErrors.permissions" class="flex items-center gap-1.5 p-4 bg-rose-50 border border-rose-100 rounded-xl animate-shake mb-6">
                        <span class="material-symbols-outlined text-rose-600 text-[20px] font-black">error</span>
                        <p class="text-sm font-black text-rose-600 leading-none">{{ validationErrors.permissions[0] }}</p>
                    </div>

                    <div v-for="(groupPermissions, groupName) in availablePermissions" :key="groupName"
                        class="space-y-4">
                        <div class="flex items-center gap-2 border-b-2 border-slate-200 pb-2">
                            <span class="material-symbols-outlined text-slate-500 text-sm">folder_open</span>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ groupName }}</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div v-for="permission in groupPermissions" :key="permission.id"
                                @click="togglePermission(permission.name)"
                                class="flex items-center justify-between p-3 rounded-xl border-2 transition-all cursor-pointer group select-none"
                                :class="permissions.includes(permission.name)
                                    ? 'bg-primary/5 border-primary/40 shadow-sm'
                                    : 'bg-white border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                        :class="permissions.includes(permission.name)
                                            ? 'bg-primary border-primary text-white shadow-sm'
                                            : 'bg-white border-slate-400 group-hover:border-primary/50 text-transparent'">
                                        <span class="material-symbols-outlined text-[14px] font-bold">check</span>
                                    </div>
                                    <span class="text-sm font-semibold transition-colors"
                                        :class="permissions.includes(permission.name) ? 'text-primary' : 'text-text-primary'">
                                        {{ permission.display_name || permission.name }}
                                    </span>
                                </div>
                                <span
                                    class="text-[10px] font-bold text-slate-300 font-mono opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ permission.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <template #footer>
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center bg-slate-50 p-4 rounded-xl gap-4">
                        <p class="text-sm text-text-secondary font-medium order-2 sm:order-1">
                            Selected: <span class="text-primary font-bold">{{ permissions.length }}</span> permissions
                        </p>
                        <div class="flex gap-3 w-full sm:w-auto order-1 sm:order-2">
                            <BaseButton variant="ghost" class="flex-1 sm:flex-none" @click="handleCancel">
                                Cancel
                            </BaseButton>
                            <BaseButton icon="shield" class="flex-1 sm:flex-none" @click="submit" :loading="isLoading">
                                Create Role
                            </BaseButton>
                        </div>
                    </div>
                </template>
            </BasePanel>
        </div>
    </BasePageContainer>
</template>
