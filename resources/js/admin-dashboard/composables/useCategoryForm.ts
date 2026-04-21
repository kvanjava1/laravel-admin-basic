import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { categoryService, type Category, type CategoryGroup } from '../services/categoryService';
import { alertService } from '../utils/sweetalert';

/**
 * useCategoryForm.ts
 * Manage state and logic for Category Create/Edit forms.
 */
export function useCategoryForm() {
    const router = useRouter();

    // Form State
    const categoryId = ref<number | null>(null);
    const name = ref('');
    const slug = ref('');
    const selectedGroupId = ref<string>('');
    const selectedParentId = ref<string>('');
    const isActive = ref<string>('1');

    // Supporting State
    const groups = ref<CategoryGroup[]>([]);
    const categories = ref<Category[]>([]);
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const validationErrors = ref<Record<string, string[]>>({});

    // Watchers
    watch(name, (newName) => {
        slug.value = newName
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    });

    watch(selectedGroupId, () => {
        if (selectedGroupId.value) {
            fetchCategories();
        }
    });

    // Actions
    const fetchGroups = async () => {
        try {
            const response = await categoryService.getGroups();
            if (response.status === 'success') {
                groups.value = response.data;
            }
        } catch (error) {
            console.error('Failed to fetch groups:', error);
        }
    };

    const fetchCategories = async () => {
        if (!selectedGroupId.value) return;
        try {
            const response = await categoryService.getCategories(parseInt(selectedGroupId.value));
            if (response.status === 'success') {
                categories.value = response.data;
            }
        } catch (error) {
            console.error('Failed to fetch categories:', error);
        }
    };

    const loadCategory = async (id: number | string) => {
        isLoading.value = true;
        try {
            const response = await categoryService.getCategory(id);
            if (response.status === 'success') {
                const cat = response.data;
                categoryId.value = cat.id;
                name.value = cat.name;
                slug.value = cat.slug;
                selectedGroupId.value = cat.category_group_id.toString();
                selectedParentId.value = cat.parent_id ? cat.parent_id.toString() : '';
                isActive.value = cat.is_active ? '1' : '0';
            }
        } catch (error) {
            alertService.errorToast('Failed to load category data');
        } finally {
            isLoading.value = false;
        }
    };

    const submit = async () => {
        isSubmitting.value = true;
        validationErrors.value = {};
        
        const payload = {
            category_group_id: selectedGroupId.value,
            parent_id: selectedParentId.value || null,
            name: name.value,
            slug: slug.value,
            is_active: isActive.value === '1'
        };

        try {
            let response;
            if (categoryId.value) {
                response = await categoryService.updateCategory(categoryId.value, payload);
            } else {
                response = await categoryService.createCategory(payload);
            }

            if (response.status === 'success') {
                alertService.successToast(categoryId.value ? 'Category updated successfully' : 'Category created successfully');
                router.push({ name: 'categories.index' });
            }
        } catch (error: any) {
            if (error.response?.status === 422) {
                validationErrors.value = error.response.data.errors;
            }
            alertService.errorToast(error.response?.data?.message || 'Operation failed');
        } finally {
            isSubmitting.value = false;
        }
    };

    // Helper for Parent Options
    const buildFlatOptions = (items: Category[], level = 0): { label: string, value: string }[] => {
        let options: { label: string, value: string }[] = [];
        items.forEach(node => {
            // Prevent self-selection in edit mode
            if (categoryId.value && node.id === categoryId.value) return;

            options.push({
                label: '— '.repeat(level) + node.name,
                value: node.id.toString()
            });
            if (node.children_recursive && node.children_recursive.length > 0) {
                options = [...options, ...buildFlatOptions(node.children_recursive, level + 1)];
            }
        });
        return options;
    };

    const groupOptions = computed(() => groups.value.map(g => ({
        label: g.name,
        value: g.id.toString()
    })));

    const parentOptions = computed(() => [
        { label: 'None (Root Category)', value: '' },
        ...buildFlatOptions(categories.value)
    ]);

    return {
        // State
        name, slug, selectedGroupId, selectedParentId, isActive,
        groups, categories, isLoading, isSubmitting, validationErrors,
        groupOptions, parentOptions, categoryId,

        // Actions
        fetchGroups, fetchCategories, loadCategory, submit, handleCancel: () => router.push({ name: 'categories.index' })
    };
}
