<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { type Category } from '../../services/categoryService';
import { useCategoryList } from '../../composables/useCategoryList';

// UI Components
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';
import ActionMenu from '../../components/ui/ActionMenu.vue';
import UserStatusBadge from '../../components/user/UserStatusBadge.vue';

// Atomic Table
import TableMain from '../../components/ui/table-atomic/TableMain.vue';
import TableHead from '../../components/ui/table-atomic/TableHead.vue';
import TableBody from '../../components/ui/table-atomic/TableBody.vue';
import TableRow from '../../components/ui/table-atomic/TableRow.vue';
import TableTh from '../../components/ui/table-atomic/TableTh.vue';
import TableTd from '../../components/ui/table-atomic/TableTd.vue';

const router = useRouter();
const {
    selectedGroupId,
    isLoading,
    categories,
    groupOptions,
    fetchGroups,
    fetchCategories,
    confirmDelete
} = useCategoryList();

onMounted(async () => {
    await fetchGroups();
    await fetchCategories();
});

watch(selectedGroupId, () => {
    fetchCategories();
});

const getRowActions = (item: Category) => [
    { label: 'Edit', icon: 'edit', handler: () => router.push({ name: 'categories.edit', params: { id: item.id } }) },
    { label: 'Add Child', icon: 'add', handler: () => router.push({ name: 'categories.create', query: { parent_id: item.id, group_id: item.category_group_id } }) },
    { label: 'Delete', icon: 'delete', colorClass: 'text-rose-600', handler: () => confirmDelete(item) },
];

</script>

<template>
    <BasePageContainer variant="wide">
        <BasePageHeader title="Category Management" subtitle="Organize your content with hierarchical categories" />

        <BasePanel title="Manage Hierarchy" icon="category" stacked-header>
            <template #actions>
                <div class="flex items-center justify-between w-full">
                    <BaseButton icon="add" size="md" @click="$router.push({ name: 'categories.create' })">
                        Add Category
                    </BaseButton>
                    <ActionMenu :actions="[]" size="md" />
                </div>
            </template>

            <!-- Compact Group Selector -->
            <template #top-content>
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                    <div class="max-w-xs space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Target Group</label>
                        <div class="relative">
                            <select 
                                :value="selectedGroupId" 
                                @change="(e) => selectedGroupId = parseInt((e.target as HTMLSelectElement).value)"
                                class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 pr-10 text-base appearance-none focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer"
                            >
                                <option v-for="opt in groupOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">expand_more</span>
                        </div>
                    </div>
                </div>
            </template>

            <div class="table-content-wrapper min-w-full">
                <TableMain>
                    <TableHead>
                        <TableRow>
                            <TableTh>Name</TableTh>
                            <TableTh>Slug</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh class="text-right">Action</TableTh>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        <!-- Recursive Level 1 -->
                        <template v-for="cat in categories" :key="cat.id">
                            <TableRow>
                                <TableTd>
                                    <div class="flex items-center gap-2 font-bold text-text-primary">
                                        <span class="material-symbols-outlined text-primary">category</span>
                                        {{ cat.name }}
                                    </div>
                                </TableTd>
                                <TableTd>{{ cat.slug }}</TableTd>
                                <TableTd>
                                    <UserStatusBadge :status="{ name: cat.is_active ? 'Active' : 'Inactive' }" />
                                </TableTd>
                                <TableTd class="text-right">
                                    <ActionMenu :actions="getRowActions(cat)" />
                                </TableTd>
                            </TableRow>

                            <!-- Recursive Level 2 -->
                            <template v-for="child in cat.children_recursive" :key="child.id">
                                <TableRow class="bg-slate-50/50">
                                    <TableTd class="pl-10">
                                        <div class="flex items-center gap-2 text-text-primary font-medium">
                                            <span class="material-symbols-outlined text-slate-400">subdirectory_arrow_right</span>
                                            {{ child.name }}
                                        </div>
                                    </TableTd>
                                    <TableTd>{{ child.slug }}</TableTd>
                                    <TableTd>
                                        <UserStatusBadge :status="{ name: child.is_active ? 'Active' : 'Inactive' }" />
                                    </TableTd>
                                    <TableTd class="text-right">
                                        <ActionMenu :actions="getRowActions(child)" />
                                    </TableTd>
                                </TableRow>

                                <!-- Recursive Level 3 -->
                                <template v-for="grandChild in child.children_recursive" :key="grandChild.id">
                                    <TableRow class="bg-white">
                                        <TableTd class="pl-20">
                                            <div class="flex items-center gap-2 text-text-secondary text-sm">
                                                <span class="material-symbols-outlined text-slate-300">subdirectory_arrow_right</span>
                                                {{ grandChild.name }}
                                            </div>
                                        </TableTd>
                                        <TableTd>{{ grandChild.slug }}</TableTd>
                                        <TableTd>
                                            <UserStatusBadge :status="{ name: grandChild.is_active ? 'Active' : 'Inactive' }" />
                                        </TableTd>
                                        <TableTd class="text-right">
                                            <ActionMenu :actions="getRowActions(grandChild)" />
                                        </TableTd>
                                    </TableRow>
                                </template>
                            </template>
                        </template>

                        <TableRow v-if="categories.length === 0 && !isLoading">
                            <TableTd colspan="4" class="py-10 text-center text-text-secondary">
                                No categories found for this group.
                            </TableTd>
                        </TableRow>

                        <TableRow v-if="isLoading">
                            <TableTd colspan="4" class="py-10 text-center text-text-secondary">
                                Loading categories...
                            </TableTd>
                        </TableRow>
                    </TableBody>
                </TableMain>
            </div>
        </BasePanel>
    </BasePageContainer>
</template>
