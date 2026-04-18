import { onMounted, ref } from 'vue';
import { mediaService, type MediaItem } from '../services/mediaService';
import { alertService } from '../utils/sweetalert';

export function useMediaList() {
    const mediaItems = ref<MediaItem[]>([]);
    const isLoading = ref(false);
    const showAdvancedFilter = ref(false);
    const advancedFilters = ref({
        title: '',
        alt_text: '',
        caption: '',
        description: '',
        category_id: '',
        tags: [] as string[],
    });
    const pagination = ref({
        currentPage: 1,
        lastPage: 1,
        perPage: 12,
        total: 0,
        from: 0,
        to: 0,
    });

    const fetchMedia = async (page = 1) => {
        isLoading.value = true;

        try {
            const response = await mediaService.getPaginated({
                page,
                per_page: pagination.value.perPage,
                ...advancedFilters.value,
            });

            if (response.success) {
                mediaItems.value = response.data.data;
                pagination.value = {
                    currentPage: response.data.current_page,
                    lastPage: response.data.last_page,
                    perPage: response.data.per_page,
                    total: response.data.total,
                    from: response.data.from ?? 0,
                    to: response.data.to ?? 0,
                };
            }
        } catch (error) {
            console.error('Failed to fetch media:', error);
            alertService.errorToast('Load Error', 'Could not load media library data.');
        } finally {
            isLoading.value = false;
        }
    };

    const handlePageChange = (page: number) => {
        fetchMedia(page);
    };

    const handleAdvancedFilter = (filters: typeof advancedFilters.value) => {
        advancedFilters.value = { ...filters };
        fetchMedia(1);
    };

    const clearFilters = () => {
        advancedFilters.value = {
            title: '',
            alt_text: '',
            caption: '',
            description: '',
            category_id: '',
            tags: [],
        };
        fetchMedia(1);
    };

    onMounted(() => {
        fetchMedia(1);
    });

    return {
        mediaItems,
        isLoading,
        showAdvancedFilter,
        advancedFilters,
        pagination,
        fetchMedia,
        handleAdvancedFilter,
        clearFilters,
        handlePageChange,
    };
}
