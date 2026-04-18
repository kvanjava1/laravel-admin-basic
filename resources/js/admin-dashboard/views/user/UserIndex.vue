<script setup lang="ts">
import { onMounted } from 'vue';
import UserFilterModal from '../../components/user/UserFilterModal.vue';
import UserDetailsModal from '../../components/user/UserDetailsModal.vue';
import UserStatusBadge from '../../components/user/UserStatusBadge.vue';

// Layout Helpers
import BasePanel from '../../components/ui/BasePanel.vue';
import TablePagination from '../../components/ui/TablePagination.vue';
import ActionMenu from '../../components/ui/ActionMenu.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import { useUserList } from '../../composables/useUserList';

// --- ATOMIC TABLE COMPONENTS ---
import TableMain from '../../components/ui/table-atomic/TableMain.vue';
import TableHead from '../../components/ui/table-atomic/TableHead.vue';
import TableBody from '../../components/ui/table-atomic/TableBody.vue';
import TableRow from '../../components/ui/table-atomic/TableRow.vue';
import TableTh from '../../components/ui/table-atomic/TableTh.vue';
import TableTd from '../../components/ui/table-atomic/TableTd.vue';

const {
    usersList, isLoading, showAdvancedFilter, showDetailsModal, selectedUser,
    filterValues, pagination, isSearching, headerActions,
    fetchUsers, clearFilters, handleDeleteUser,
    getRowActions, handleFilter, handleReset, handlePageChange
} = useUserList();


onMounted(() => {
    fetchUsers();
});

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatTime = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <BasePageContainer variant="wide">
        <!-- Header Section -->
        <BasePageHeader title="User Management" subtitle="Manage and define system users" />

        <BasePanel title="All Users" icon="group" stacked-header>
            <template #actions>
                <div class="flex items-center justify-between w-full">
                    <BaseButton icon="add" size="md" @click="$router.push({ name: 'users.create' })">
                        Add User
                    </BaseButton>
                    <ActionMenu :actions="headerActions" size="md" />
                </div>
            </template>

            <!-- Compact Search Indicator -->
            <template #top-content>
                <div v-if="isSearching" class="px-6 py-1.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <span class="material-symbols-outlined text-md">search_insights</span>
                        <span class="animate-pulse text-primary">Search Active</span>
                    </div>
                    <button @click="clearFilters" class="text-[10px] font-black text-primary hover:text-primary-dark transition-colors uppercase tracking-tight">Clear Filters</button>
                </div>
            </template>

            <!-- Table Wrapper -->
            <div class="table-content-wrapper min-w-full">
                <!-- Loading State -->
                <div v-if="isLoading && usersList.length === 0" class="py-20 text-center">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary">sync</span>
                    <p class="mt-2 text-text-secondary">Loading users...</p>
                </div>

                <!-- 
                  REUSABLE ATOMIC STRUCTURE:
                  This looks like normal HTML but uses our pre-styled components!
                -->
                <TableMain v-else>
                    <TableHead>
                        <TableRow>
                            <TableTh class="w-16 whitespace-nowrap text-center">No.</TableTh>
                            <TableTh>User</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh>Created At</TableTh>
                            <TableTh>Updated At</TableTh>
                            <TableTh class="text-right">Action</TableTh>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        <TableRow v-for="(user, index) in usersList" :key="user.id">
                            <TableTd class="text-center font-medium text-sm text-slate-500">
                                {{ (pagination.currentPage - 1) * pagination.perPage + index + 1 }}
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-9 w-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shadow-sm">
                                        <img v-if="user.avatar" :src="'/storage/' + user.avatar"
                                            class="h-full w-full object-cover">
                                        <template v-else>
                                            {{ user.name.charAt(0) }}
                                        </template>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-text-primary text-base">{{ user.name }}</span>
                                        <span class="text-sm text-text-secondary/60">{{ user.email }}</span>
                                    </div>
                                </div>
                            </TableTd>
                            <TableTd>
                                <UserStatusBadge :status="user.status" />
                            </TableTd>
                            <TableTd>
                                <div class="flex flex-col">
                                    <span class="font-medium text-text-primary text-sm">{{ formatDate(user.created_at) }}</span>
                                    <span class="text-xs text-text-secondary">{{ formatTime(user.created_at) }}</span>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="flex flex-col">
                                    <span class="font-medium text-text-primary text-sm">{{ formatDate(user.updated_at) }}</span>
                                    <span class="text-xs text-text-secondary">{{ formatTime(user.updated_at) }}</span>
                                </div>
                            </TableTd>
                            <TableTd class="text-right">
                                <!-- Generic ActionMenu for Row -->
                                <ActionMenu :actions="getRowActions(user)" :index="index" :total="usersList.length"
                                    size="md" />
                            </TableTd>
                        </TableRow>

                        <!-- Empty State -->
                        <TableRow v-if="usersList.length === 0">
                            <TableTd colspan="6" class="text-center py-20 text-text-secondary">
                                <span class="material-symbols-outlined text-4xl block mb-2">person_search</span>
                                No users found matching your filters.
                            </TableTd>
                        </TableRow>
                    </TableBody>
                </TableMain>
            </div>

            <template #footer>
                <TablePagination :total="pagination.total" :current-page="pagination.currentPage"
                    :last-page="pagination.lastPage" :per-page="pagination.perPage" :from="pagination.from"
                    :to="pagination.to" @on-change="handlePageChange" />
            </template>
        </BasePanel>

        <!-- Advanced Filter Modal -->
        <UserFilterModal :show="showAdvancedFilter" @close="showAdvancedFilter = false" @filter="handleFilter"
            @reset="handleReset" />

        <!-- User Details Modal -->
        <UserDetailsModal :show="showDetailsModal" :user="selectedUser" @close="showDetailsModal = false" />
    </BasePageContainer>
</template>
