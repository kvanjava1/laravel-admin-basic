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

const {
    name, slug, selectedGroupId, selectedParentId, isActive,
    groupOptions, parentOptions, isSubmitting,
    fetchGroups, fetchCategories, submit, handleCancel
} = useCategoryForm();

onMounted(async () => {
    // Initial data from query
    if (route.query.group_id) selectedGroupId.value = route.query.group_id as string;
    if (route.query.parent_id) selectedParentId.value = route.query.parent_id as string;

    await fetchGroups();
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
        <BasePageHeader title="Add New Category" back-label="Back to Categories" back-route-name="categories.index" />

        <div class="form-content-wrapper space-y-6">
            <!-- 1. Category Scope -->
            <BasePanel title="Category Placement">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <BaseSelect 
                        label="Target Group" 
                        placeholder="Select Group" 
                        v-model="selectedGroupId"
                        :options="groupOptions" 
                    />
                    <BaseSelect 
                        label="Parent Category" 
                        placeholder="None (Root)" 
                        v-model="selectedParentId"
                        :options="parentOptions" 
                    />
                </div>
                <p class="text-xs text-text-secondary mt-4 italic">
                    Select where this category will belong in the hierarchy.
                </p>
            </BasePanel>

            <!-- 2. Basic Information -->
            <BasePanel title="Basic Information">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <BaseInput label="Category Name" icon="category" v-model="name" placeholder="e.g. Technology" />
                    <BaseInput label="URL Slug" icon="link" v-model="slug" placeholder="e.g. technology" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2 mt-4">
                    <BaseSelect 
                        label="Status" 
                        v-model="isActive"
                        :options="statusOptions" 
                    />
                </div>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 bg-slate-50 p-4 rounded-xl">
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="handleCancel">
                            Cancel
                        </BaseButton>
                        <BaseButton icon="add" class="w-full sm:w-auto" @click="submit" :loading="isSubmitting">
                            Create Category
                        </BaseButton>
                    </div>
                </template>
            </BasePanel>
        </div>
    </BasePageContainer>
</template>
