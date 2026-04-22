import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { articleService } from '../services/articleService';
import { alertService } from '../utils/sweetalert';

export function useArticleList() {
    const router = useRouter();

    // Core State
    const articlesList = ref<any[]>([]);
    const isLoading = ref(false);
    const showAdvancedFilter = ref(false);
    const showDetailsModal = ref(false);
    const selectedArticle = ref<any | null>(null);

    // Filter State
    const filterValues = ref({
        search: '',
        status: '',
        category: '',
        author: '',
        published_from: '',
        published_to: ''
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
            filterValues.value.category ||
            filterValues.value.author ||
            filterValues.value.published_from ||
            filterValues.value.published_to
        );
    });

    const fetchArticles = async (page = 1) => {
        isLoading.value = true;
        try {
            const response = await articleService.getPaginated({
                page,
                per_page: pagination.value.perPage,
                ...filterValues.value
            });

            // Laravel default pagination structure
            const paginator = response;
            articlesList.value = paginator.data || [];

            pagination.value = {
                currentPage: paginator.current_page || 1,
                lastPage: paginator.last_page || 1,
                perPage: paginator.per_page || 10,
                total: paginator.total || 0,
                from: paginator.from || 1,
                to: paginator.to || (articlesList.value?.length || 0)
            };
        } catch (error) {
            console.error('Failed to fetch articles:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const clearFilters = () => {
        filterValues.value = {
            search: '',
            status: '',
            category: '',
            author: '',
            published_from: '',
            published_to: ''
        };
        fetchArticles(1);
    };

    const handleDeleteArticle = async (article: any) => {
        const result = await alertService.confirm({
            title: 'Delete Article?',
            text: `Are you sure you want to delete "${article.title}"? This will move it to trash.`,
            confirmButtonText: 'Yes, Delete',
            danger: true
        });

        if (!result.isConfirmed) return;

        try {
            const response = await articleService.destroy(article.id);
            if (response.success || response) {
                alertService.successToast(`Article deleted successfully.`);
                fetchArticles(pagination.value.currentPage);
            }
        } catch (err: any) {
            alertService.errorToast('Failed to delete article');
            console.error(err);
        }
    };

    const showArticleDetails = (article: any) => {
        selectedArticle.value = article;
        showDetailsModal.value = true;
    };

    const getRowActions = (article: any) => [
        { label: 'View Detail', icon: 'visibility', handler: () => showArticleDetails(article) },
        { label: 'Edit Content', icon: 'edit', handler: () => router.push({ name: 'articles.edit', params: { id: article.id } }) },
        { label: 'Delete Article', icon: 'delete', colorClass: 'text-rose-600', handler: () => handleDeleteArticle(article) },
    ];

    const headerActions = computed(() => [
        {
            label: showAdvancedFilter.value ? 'Hide Filter' : 'Show Filter',
            icon: showAdvancedFilter.value ? 'filter_list_off' : 'filter_list',
            handler: () => showAdvancedFilter.value = !showAdvancedFilter.value
        }
    ]);

    const handlePageChange = (page: number) => {
        fetchArticles(page);
    };

    return {
        // State
        articlesList, isLoading, showAdvancedFilter, showDetailsModal, selectedArticle,
        filterValues, pagination, isSearching, headerActions,

        // Actions
        fetchArticles, clearFilters, handleDeleteArticle, showArticleDetails,
        getRowActions, handlePageChange
    };
}
