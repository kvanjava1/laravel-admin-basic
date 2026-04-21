<script setup lang="ts">
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCategoryForm } from '../../composables/useCategoryForm';

// UI Components
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';

const route = useRoute();
const categoryId = route.params.id as string;

const {
    name, slug, selectedGroupId, selectedParentId, isActive,
    groupOptions, parentOptions, isLoading, isSubmitting, validationErrors,
    fetchGroups, fetchCategories, loadCategory, submit, handleCancel
} = useCategoryForm();

onMounted(async () => {
    await fetchGroups();
    
    // Load Category Data
    await loadCategory(categoryId);

    if (selectedGroupId.value) {
        await fetchCategories();
    }
});

const statusOptions = [
    { label: 'Active', value: '1' },
    { label: 'Inactive', value: '0' }
];
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader 
            :title="isLoading ? 'Loading...' : `Edit Category: ${name}`" 
            back-label="Back to Categories" 
            back-route-name="categories.index" 
        />

        <div class="form-content-wrapper space-y-6">
            <!-- Loading State -->
            <BasePanel v-if="isLoading" title="Loading Category Data">
                <div class="flex justify-center py-10">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                </div>
            </BasePanel>

            <template v-else>
                <!-- 1. Category Scope -->
                <BasePanel title="Category Placement">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                        <BaseSelect 
                            label="Target Group" 
                            placeholder="Select Group" 
                            v-model="selectedGroupId"
                            :options="groupOptions" 
                            :error="validationErrors.category_group_id?.[0]"
                        />
                        <BaseSelect 
                            label="Parent Category" 
                            placeholder="None (Root)" 
                            v-model="selectedParentId"
                            :options="parentOptions" 
                            :error="validationErrors.parent_id?.[0]"
                        />
                    </div>
                    <p class="text-xs text-text-secondary mt-4 italic">
                        Select where this category will belong in the hierarchy.
                    </p>
                </BasePanel>
 
                <!-- 2. Basic Information -->
                <BasePanel title="Basic Information">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                        <BaseInput 
                            label="Category Name" 
                            icon="category" 
                            v-model="name" 
                            placeholder="e.g. Technology" 
                            :error="validationErrors.name?.[0]"
                        />
                        <BaseInput 
                            label="URL Slug" 
                            icon="link" 
                            v-model="slug" 
                            placeholder="e.g. technology" 
                            :error="validationErrors.slug?.[0]"
                        />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2 mt-4">
                        <BaseSelect 
                            label="Status" 
                            v-model="isActive"
                            :options="statusOptions" 
                            :error="validationErrors.is_active?.[0]"
                        />
                    </div>

                    <template #footer>
                        <div class="flex flex-col sm:flex-row justify-end gap-3 bg-slate-50 p-4 rounded-xl">
                            <BaseButton variant="ghost" class="w-full sm:w-auto" @click="handleCancel">
                                Cancel
                            </BaseButton>
                            <BaseButton icon="save" class="w-full sm:w-auto" @click="submit" :loading="isSubmitting">
                                Save Changes
                            </BaseButton>
                        </div>
                    </template>
                </BasePanel>
            </template>
        </div>
    </BasePageContainer>
</template>
