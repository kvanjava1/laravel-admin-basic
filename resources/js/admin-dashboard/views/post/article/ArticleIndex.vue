<script setup lang="ts">
import { onMounted } from 'vue';
import ArticleFilterModal from '../../../components/article/ArticleFilterModal.vue';
import ArticleDetailsModal from '../../../components/article/ArticleDetailsModal.vue';
import ArticleStatusBadge from '../../../components/article/ArticleStatusBadge.vue';

// Layout Helpers
import BasePanel from '../../../components/ui/BasePanel.vue';
import TablePagination from '../../../components/ui/TablePagination.vue';
import ActionMenu from '../../../components/ui/ActionMenu.vue';
import BaseButton from '../../../components/ui/BaseButton.vue';
import BasePageHeader from '../../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../../components/ui/BasePageContainer.vue';
import { useArticleList } from '../../../composables/useArticleList';

// --- ATOMIC TABLE COMPONENTS ---
import TableMain from '../../../components/ui/table-atomic/TableMain.vue';
import TableHead from '../../../components/ui/table-atomic/TableHead.vue';
import TableBody from '../../../components/ui/table-atomic/TableBody.vue';
import TableRow from '../../../components/ui/table-atomic/TableRow.vue';
import TableTh from '../../../components/ui/table-atomic/TableTh.vue';
import TableTd from '../../../components/ui/table-atomic/TableTd.vue';

const {
    articlesList, isLoading, showAdvancedFilter, showDetailsModal, selectedArticle,
    filterValues, pagination, isSearching, headerActions,
    fetchArticles, clearFilters, showArticleDetails,
    getRowActions, handlePageChange
} = useArticleList();

onMounted(() => {
    fetchArticles();
});

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const handleFilter = (filters: any) => {
    Object.assign(filterValues.value, filters);
    fetchArticles(1);
};

const handleReset = () => {
    clearFilters();
};
</script>

<template>
    <BasePageContainer variant="wide">
        <!-- Header Section -->
        <BasePageHeader title="Article Management" subtitle="Create and manage editorial posts" />

        <BasePanel title="All Articles" icon="article" stacked-header>
            <template #actions>
                <div class="flex items-center justify-between w-full">
                    <BaseButton icon="add" size="md" @click="$router.push({ name: 'articles.create' })">
                        Create Article
                    </BaseButton>
                    <ActionMenu :actions="headerActions" size="md" />
                </div>
            </template>

            <!-- Compact Search Indicator -->
            <template #top-content>
                <div v-if="isSearching"
                    class="px-6 py-1.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <span class="material-symbols-outlined text-md">search_insights</span>
                        <span class="animate-pulse text-primary">Filter Active</span>
                    </div>
                    <button @click="clearFilters"
                        class="text-[10px] font-black text-primary hover:text-primary-dark transition-colors uppercase tracking-tight">Clear
                        Filters</button>
                </div>
            </template>

            <!-- Table Wrapper -->
            <div class="table-content-wrapper min-w-full">
                <!-- Loading State -->
                <div v-if="isLoading && articlesList.length === 0" class="py-20 text-center">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary">sync</span>
                    <p class="mt-2 text-text-secondary">Loading articles...</p>
                </div>

                <TableMain v-else>
                    <TableHead>
                        <TableRow>
                            <TableTh class="w-16 whitespace-nowrap text-center">No.</TableTh>
                            <TableTh>Article</TableTh>
                            <TableTh>Category</TableTh>
                            <TableTh>Status</TableTh>
                            <TableTh>Published At</TableTh>
                            <TableTh class="text-right">Action</TableTh>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        <TableRow v-for="(article, index) in articlesList" :key="article.id" class="group">
                            <TableTd class="text-center font-medium text-sm text-slate-500">
                                {{ (pagination.currentPage - 1) * pagination.perPage + index + 1 }}
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-4 group/item cursor-pointer" @click="showArticleDetails(article)">
                                    <!-- Thumbnail Preview -->
                                    <div class="h-12 w-12 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 shadow-sm group-hover/item:border-primary/50 transition-colors">
                                        <img v-if="article.featured_image" :src="article.featured_image.original_url" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <span class="material-symbols-outlined text-slate-300 text-xl">image</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-slate-700 text-[15px] line-clamp-1 group-hover/item:text-primary transition-colors">{{
                                            article.title }}</span>
                                        <span
                                            class="text-[10px] text-slate-400 uppercase font-black tracking-widest mt-0.5">By
                                            {{
                                            article.author?.name || 'Administrator' }}</span>
                                    </div>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wide shadow-sm border border-black/5">
                                    <span class="h-1.5 w-1.5 rounded-full mr-2 bg-slate-400"></span>
                                    {{ article.category?.name || 'Uncategorized' }}
                                </div>
                            </TableTd>
                            <TableTd>
                                <ArticleStatusBadge :status="article.status" />
                            </TableTd>
                            <TableTd>
                                <span class="font-medium text-text-primary text-sm">{{ formatDate(article.published_at)
                                    }}</span>
                            </TableTd>
                            <TableTd class="text-right">
                                <ActionMenu :actions="getRowActions(article)" :index="index"
                                    :total="articlesList.length" size="md" />
                            </TableTd>
                        </TableRow>

                        <!-- Empty State -->
                        <TableRow v-if="articlesList.length === 0">
                            <TableTd colspan="6" class="text-center py-20 text-text-secondary">
                                <span class="material-symbols-outlined text-4xl block mb-2">find_in_page</span>
                                No articles found.
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
        <ArticleFilterModal :show="showAdvancedFilter" @close="showAdvancedFilter = false" @filter="handleFilter"
            @reset="handleReset" />

        <!-- Details Modal -->
        <ArticleDetailsModal :show="showDetailsModal" :article="selectedArticle" @close="showDetailsModal = false" />
    </BasePageContainer>
</template>
