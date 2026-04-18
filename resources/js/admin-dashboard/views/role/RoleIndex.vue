<script setup lang="ts">
/**
 * RoleIndex.vue
 * Dashboard page for managing user roles (Frontend only with mockup data).
 */
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { roleService, type Role } from '../../services/roleService';

// UI Components
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import TableMain from '../../components/ui/table-atomic/TableMain.vue';
import TableHead from '../../components/ui/table-atomic/TableHead.vue';
import TableBody from '../../components/ui/table-atomic/TableBody.vue';
import TableRow from '../../components/ui/table-atomic/TableRow.vue';
import TableTh from '../../components/ui/table-atomic/TableTh.vue';
import TableTd from '../../components/ui/table-atomic/TableTd.vue';
import TablePagination from '../../components/ui/TablePagination.vue';
import ActionMenu from '../../components/ui/ActionMenu.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import { alertService } from '../../utils/sweetalert';

// Feature Components
import RoleDetailsModal from '../../components/role/RoleDetailsModal.vue';
import RoleFilterModal from '../../components/role/RoleFilterModal.vue';

const router = useRouter();

// State
const roles = ref<Role[]>([]);
const selectedRole = ref<Role | null>(null);
const isDetailsModalOpen = ref(false);
const isFilterModalOpen = ref(false);
const isLoading = ref(false);

const filters = ref({
    search: '',
    permissions: [] as number[],
    created_at_from: '',
    created_at_to: '',
    updated_at_from: '',
    updated_at_to: ''
});

const isSearching = computed(() => {
    return !!(
        filters.value.search || 
        filters.value.permissions.length > 0 ||
        filters.value.created_at_from ||
        filters.value.created_at_to ||
        filters.value.updated_at_from ||
        filters.value.updated_at_to
    );
});

// Pagination State
const currentPage = ref(1);
const perPage = ref(10);
const totalRoles = ref(0);
const lastPage = ref(1);
const from = ref(1);
const to = ref(1);

// UI Feedback State

const fetchRoles = async () => {
    isLoading.value = true;
    try {
        const response = await roleService.index({
            page: currentPage.value,
            per_page: perPage.value,
            ...filters.value
        });

        roles.value = response.data.data;
        totalRoles.value = response.data.total;
        lastPage.value = response.data.last_page;
        from.value = response.data.from;
        to.value = response.data.to;
    } catch (error) {
        console.error('Failed to fetch roles:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchRoles();
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchRoles();
};

const handleAddRole = () => {
    router.push({ name: 'roles.create' });
};

const handleFilter = (newFilters: any) => {
    filters.value = newFilters;
    currentPage.value = 1;
    fetchRoles();
};

const handleResetFilters = () => {
    filters.value = {
        search: '',
        permissions: [],
        created_at_from: '',
        created_at_to: '',
        updated_at_from: '',
        updated_at_to: ''
    };
    currentPage.value = 1;
    fetchRoles();
};

const headerActions = [
    {
        label: 'Show Filter',
        icon: 'filter_list',
        handler: () => isFilterModalOpen.value = true
    }
];

const handleDeleteRole = async (role: Role) => {
    const result = await alertService.confirm({
        title: 'Delete Role?',
        text: `Are you sure you want to delete the role "${role.name}"? This action cannot be undone.`,
        confirmButtonText: 'Yes, Delete',
        danger: true
    });

    if (!result.isConfirmed) return;

    try {
        const response = await roleService.destroy(role.id);
        if (response.success) {
            alertService.successToast(`Role "${role.name}" deleted successfully.`);
            fetchRoles();
        }
    } catch (err: any) {
        const response = err.response?.data;
        alertService.errorToast(
            response?.message || err.message || 'Server Error',
            response?.description || ''
        );
        console.error('Failed to delete role:', err);
    }
};

const handleViewDetail = (role: Role) => {
    selectedRole.value = role;
    isDetailsModalOpen.value = true;
};

const handleEditRole = (role: Role) => {
    router.push({ name: 'roles.edit', params: { id: role.id } });
};

const getRoleActions = (role: Role) => [
    {
        label: 'Detail Roles',
        icon: 'visibility',
        handler: () => handleViewDetail(role)
    },
    {
        label: 'Edit Role',
        icon: 'edit',
        handler: () => handleEditRole(role)
    },
    {
        label: 'Delete Role',
        icon: 'delete',
        colorClass: 'text-rose-600',
        handler: () => handleDeleteRole(role)
    }
];
</script>

<template>
    <BasePageContainer variant="wide">
        <!-- Header Section -->
        <BasePageHeader title="Roles Management" subtitle="Manage and define system permissions" />

        <!-- Table Section -->
        <BasePanel title="Available Roles" stacked-header>
            <template #actions>
                <div class="flex items-center justify-between w-full">
                    <BaseButton icon="add" size="md" @click="handleAddRole">
                        Add Roles
                    </BaseButton>
                    <ActionMenu :actions="headerActions" size="md" />
                </div>
            </template>

            <!-- Compact Search Indicator -->
            <template #top-content>
                <div v-if="isSearching" class="px-6 py-1.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between transition-all">
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <span class="material-symbols-outlined text-[14px] text-primary animate-pulse">search_insights</span>
                        <span>Filtering Active</span>
                    </div>
                    <button @click="handleResetFilters" class="text-[10px] font-black text-primary hover:text-primary-dark transition-colors uppercase tracking-tight flex items-center gap-1">
                        Clear ALL
                        <span class="material-symbols-outlined text-[12px]">close</span>
                    </button>
                </div>
            </template>

            <!-- Table Wrapper -->
            <div class="table-content-wrapper min-w-full">
                <!-- Loading State -->
                <div v-if="isLoading && roles.length === 0" class="py-20 text-center">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary">sync</span>
                    <p class="mt-2 text-text-secondary">Loading roles...</p>
                </div>

                <TableMain v-else>
                    <TableHead>
                        <TableRow>
                            <TableTh class="w-16 whitespace-nowrap text-center">No.</TableTh>
                            <TableTh>Role Name</TableTh>
                            <TableTh>Created At</TableTh>
                            <TableTh>Updated At</TableTh>
                            <TableTh class="text-right">Action</TableTh>
                        </TableRow>
                    </TableHead>

                    <TableBody>
                        <TableRow v-for="(role, index) in roles" :key="role.id">
                            <TableTd class="text-center font-medium text-sm text-slate-500">
                                {{ from + index }}
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-9 w-9 rounded-xl bg-primary/5 flex items-center justify-center text-primary border border-primary/10 transition-transform">
                                        <span class="material-symbols-outlined text-[20px]">shield_person</span>
                                    </div>
                                    <span class="font-bold text-text-primary text-base">{{ role.name }}</span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="flex flex-col">
                                    <span class="font-medium text-text-primary text-sm">{{ formatDate(role.created_at)
                                    }}</span>
                                    <span class="text-xs text-text-secondary">{{ formatTime(role.created_at) }}</span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="flex flex-col">
                                    <span class="font-medium text-text-primary text-sm">{{ formatDate(role.updated_at)
                                    }}</span>
                                    <span class="text-xs text-text-secondary">{{ formatTime(role.updated_at) }}</span>
                                </div>
                            </TableTd>
                            <TableTd class="text-right">
                                <ActionMenu :actions="getRoleActions(role)" :index="index" :total="roles.length"
                                    size="md" />
                            </TableTd>
                        </TableRow>

                        <!-- Empty State -->
                        <TableRow v-if="roles.length === 0">
                            <TableTd colspan="5" class="text-center py-20 text-text-secondary">
                                <span class="material-symbols-outlined text-4xl block mb-2">admin_panel_settings</span>
                                No roles found matching your filters.
                            </TableTd>
                        </TableRow>

                    </TableBody>
                </TableMain>
            </div>

            <template #footer>
                <TablePagination :total="totalRoles" :per-page="perPage" :current-page="currentPage"
                    :last-page="lastPage" :from="from" :to="to" @on-change="handlePageChange" />
            </template>
        </BasePanel>

        <!-- Modals -->
        <RoleDetailsModal :show="isDetailsModalOpen" :role="selectedRole" @close="isDetailsModalOpen = false" />

        <RoleFilterModal :show="isFilterModalOpen" @close="isFilterModalOpen = false" @filter="handleFilter"
            @reset="handleResetFilters" />
    </BasePageContainer>
</template>
