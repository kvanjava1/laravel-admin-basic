import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { userService } from '../services/userService';
import { alertService } from '../utils/sweetalert';

export function useUserList() {
    const router = useRouter();
    
    // Core State
    const usersList = ref<any[]>([]);
    const isLoading = ref(false);
    const showAdvancedFilter = ref(false);
    const showDetailsModal = ref(false);
    const selectedUser = ref<any | null>(null);

    // Filter State
    const filterValues = ref({
        search: '',
        status: '',
        created_from: '',
        created_to: '',
        updated_from: '',
        updated_to: ''
    });

    // Pagination State
    const pagination = ref({
        currentPage: 1,
        lastPage: 1,
        perPage: 10,
        total: 0,
        from: 0,
        to: 0
    });

    const isSearching = computed(() => {
        return !!(
            filterValues.value.search ||
            filterValues.value.status ||
            filterValues.value.created_from ||
            filterValues.value.created_to ||
            filterValues.value.updated_from ||
            filterValues.value.updated_to
        );
    });

    const fetchUsers = async (page = 1) => {
        isLoading.value = true;
        try {
            const response = await userService.getPaginated({
                page,
                per_page: pagination.value.perPage,
                ...filterValues.value
            });

            if (response.success) {
                usersList.value = response.data.data;
                pagination.value = {
                    currentPage: response.data.current_page,
                    lastPage: response.data.last_page,
                    perPage: response.data.per_page,
                    total: response.data.total,
                    from: response.data.from,
                    to: response.data.to
                };
            }
        } catch (error) {
            console.error('Failed to fetch users:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const clearFilters = () => {
        filterValues.value = {
            search: '',
            status: '',
            created_from: '',
            created_to: '',
            updated_from: '',
            updated_to: ''
        };
        fetchUsers(1);
    };

    const handleDeleteUser = async (user: any) => {
        const result = await alertService.confirm({
            title: 'Delete User?',
            text: `Are you sure you want to delete "${user.name}"? This action cannot be undone.`,
            confirmButtonText: 'Yes, Delete',
            danger: true
        });

        if (!result.isConfirmed) return;

        try {
            const response = await userService.destroy(user.id);
            if (response.success) {
                alertService.successToast(`User "${user.name}" deleted successfully.`);
                fetchUsers(pagination.value.currentPage);
            }
        } catch (err: any) {
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                response?.description || ''
            );
            console.error('Failed to delete user:', err);
        }
    };

    const showUserDetails = (user: any) => {
        selectedUser.value = user;
        showDetailsModal.value = true;
    };

    const getRowActions = (user: any) => [
        { label: 'View Detail', icon: 'visibility', handler: () => showUserDetails(user) },
        { label: 'Edit Profile', icon: 'edit', handler: () => router.push({ name: 'users.edit', params: { id: user.id } }) },
        {
            label: 'Edit Status',
            icon: 'gavel',
            colorClass: user.status?.name === 'Banned' ? 'text-rose-600' : 'text-amber-600',
            divider: true,
            handler: () => router.push({ name: 'users.status', params: { id: user.id } })
        },
        { label: 'Delete User', icon: 'delete', colorClass: 'text-rose-600', handler: () => handleDeleteUser(user) },
    ];

    const headerActions = computed(() => [
        {
            label: showAdvancedFilter.value ? 'Hide Filter' : 'Show Filter',
            icon: showAdvancedFilter.value ? 'filter_list_off' : 'filter_list',
            handler: () => showAdvancedFilter.value = !showAdvancedFilter.value
        }
    ]);

    const handleFilter = (filters: typeof filterValues.value) => {
        filterValues.value = filters;
        fetchUsers(1);
    };

    const handleReset = () => {
        clearFilters();
    };

    const handlePageChange = (page: number) => {
        fetchUsers(page);
    };

    return {
        // State
        usersList, isLoading, showAdvancedFilter, showDetailsModal, selectedUser,
        filterValues, pagination, isSearching, headerActions,
        
        // Actions
        fetchUsers, clearFilters, handleDeleteUser, showUserDetails,
        getRowActions, handleFilter, handleReset, handlePageChange
    };
}
