import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { articleService } from '../services/articleService';
import { tagService } from '../services/tagService';
import { alertService } from '../utils/sweetalert';

export function useArticleForm() {
    const router = useRouter();
    const isLoading = ref(false);

    // Form State
    const form = ref({
        title: '',
        slug: '',
        content: '',
        excerpt: '',
        category_id: '',
        tags: [] as string[],
        featured_image: null as File | null,
        featured_image_url: null as string | null,
        seo_title: '',
        seo_description: '',
        status: 'Draft'
    });

    // Reference Data
    const categoryOptions = ref<any[]>([]);
    const tagSuggestions = ref<string[]>([]);
    const isLoadingCategories = ref(false);

    const fetchTags = async () => {
        try {
            const response = await tagService.getOptions();
            if (response.success || response.data) {
                const data = response.data || response;
                tagSuggestions.value = data.map((t: any) => t.name);
            }
        } catch (error) {
            console.error('Failed to fetch tags', error);
        }
    };

    const fetchCategories = async () => {
        isLoadingCategories.value = true;
        try {
            // In real app, we would call an API that filters by group_id=1
            // For now, we use articleService.getStatuses as a placeholder or wait for a specific category service
            // Let's assume we have a category service or just mock it here
            categoryOptions.value = [
                { label: 'Select Category', value: '' },
                { label: 'Technology', value: 1 },
                { label: 'Lifestyle', value: 2 },
                { label: 'Business', value: 3 },
            ];
        } catch (error) {
            console.error('Failed to fetch categories', error);
        } finally {
            isLoadingCategories.value = false;
        }
    };

    const handleImageSelect = (file: File) => {
        form.value.featured_image = file;
        form.value.featured_image_url = URL.createObjectURL(file);
    };

    const generateSlug = () => {
        if (!form.value.title) return;
        form.value.slug = form.value.title
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    };

    const submit = async (status: string = 'Draft') => {
        form.value.status = status;
        isLoading.value = true;
        
        try {
            const response = await articleService.store(form.value);
            if (response.success || response) {
                alertService.successToast(`Article ${status === 'Published' ? 'published' : 'saved as draft'} successfully.`);
                router.push({ name: 'articles.index' });
            }
        } catch (error: any) {
            alertService.errorToast('Failed to save article');
            console.error(error);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        form,
        isLoading,
        categoryOptions,
        tagSuggestions,
        isLoadingCategories,
        fetchCategories,
        fetchTags,
        handleImageSelect,
        generateSlug,
        submit
    };
}
