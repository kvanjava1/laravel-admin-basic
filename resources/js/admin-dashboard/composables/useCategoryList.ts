import { ref, computed } from 'vue';
import { categoryService, type Category, type CategoryGroup } from '../services/categoryService';
import { alertService } from '../utils/sweetalert';

/**
 * useCategoryList.ts
 * Manage state and logic for Category Management list view.
 */
export function useCategoryList() {
    const selectedGroupId = ref<number>(1);
    const isLoading = ref(false);
    const groups = ref<CategoryGroup[]>([]);
    const categories = ref<Category[]>([]);

    const groupOptions = computed(() => groups.value.map(g => ({
        label: g.name,
        value: g.id.toString()
    })));

    const fetchGroups = async () => {
        try {
            const response = await categoryService.getGroups();
            if (response.status === 'success') {
                groups.value = response.data;
                if (groups.value.length > 0 && !selectedGroupId.value) {
                    selectedGroupId.value = groups.value[0].id;
                }
            }
        } catch (error) {
            console.error('Failed to fetch groups:', error);
        }
    };

    const fetchCategories = async () => {
        if (!selectedGroupId.value) return;
        isLoading.value = true;
        try {
            const response = await categoryService.getCategories(selectedGroupId.value);
            if (response.status === 'success') {
                categories.value = response.data;
            }
        } catch (error) {
            console.error('Failed to fetch categories:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const confirmDelete = async (item: Category) => {
        const result = await alertService.confirm({
            title: 'Delete Category?',
            text: `Are you sure you want to delete "${item.name}"? This will also affect its children if any.`,
            icon: 'warning',
            confirmButtonText: 'Yes, delete it',
            danger: true
        });

        if (result.isConfirmed) {
            try {
                const response = await categoryService.deleteCategory(item.id);
                if (response.status === 'success') {
                    alertService.successToast('Category deleted successfully');
                    await fetchCategories();
                }
            } catch (error) {
                alertService.errorToast('Failed to delete category');
            }
        }
    };

    return {
        selectedGroupId, isLoading, groups, categories, groupOptions,
        fetchGroups, fetchCategories, confirmDelete
    };
}
