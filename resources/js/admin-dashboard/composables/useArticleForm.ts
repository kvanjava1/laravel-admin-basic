import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { articleService } from '../services/articleService';
import { tagService } from '../services/tagService';
import { categoryService } from '../services/categoryService';
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
        featured_image_id: null as number | null,
        featured_image_url: null as string | null,
        featured_image_title: '',
        featured_image_alt: '',
        featured_image_caption: '',
        seo_focus_keyword: '',
        seo_title: '',
        seo_description: '',
        status: 'Draft'
    });

    // SEO Analysis Logic
    const seoAnalysis = computed(() => {
        const keyword = form.value.seo_focus_keyword.toLowerCase().trim();
        if (!keyword) return null;

        const words = keyword.split(' ').filter(w => w.length > 2);
        const checkMatch = (text: string) => {
            if (!text) return false;
            const lowerText = text.toLowerCase();
            // Check if exact phrase exists OR all words exist
            return lowerText.includes(keyword) || words.every(word => lowerText.includes(word));
        };

        return {
            inTitle: checkMatch(form.value.title),
            inSlug: checkMatch(form.value.slug.replace(/-/g, ' ')),
            inDescription: checkMatch(form.value.seo_description),
            inContent: checkMatch(form.value.content.replace(/<[^>]*>/g, ' ')),
        };
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
            // 1. Get all groups to find the 'artikel' group ID
            const groupsResponse = await categoryService.getGroups();
            const groups = groupsResponse.data || groupsResponse;
            const articleGroup = groups.find((g: any) => g.slug === 'artikel');

            if (articleGroup) {
                // 2. Get categories for this group
                const categoriesResponse = await categoryService.getCategories(articleGroup.id);
                const categories = categoriesResponse.data || categoriesResponse;
                
                categoryOptions.value = [
                    { label: 'Select Category', value: '' },
                    ...categories.map((c: any) => ({
                        label: c.name,
                        value: c.id
                    }))
                ];
            }
        } catch (error) {
            console.error('Failed to fetch categories', error);
            categoryOptions.value = [{ label: 'Error loading categories', value: '' }];
        } finally {
            isLoadingCategories.value = false;
        }
    };

    const validationErrors = ref<Record<string, string[]>>({});

    const generateSlug = () => {
        if (!form.value.title) return;
        form.value.slug = form.value.title
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        
        // Clear slug error when regenerated
        if (validationErrors.value.slug) delete validationErrors.value.slug;
    };

    const isEditMode = ref(false);
    const currentArticleId = ref<number | null>(null);

    const loadArticle = async (id: number) => {
        isLoading.value = true;
        isEditMode.value = true;
        currentArticleId.value = id;
        try {
            const response = await articleService.show(id);
            const article = response.data || response;
            
            form.value = {
                title: article.title || '',
                slug: article.slug || '',
                content: article.content || '',
                excerpt: article.excerpt || '',
                category_id: article.category_id || '',
                tags: article.tags ? article.tags.map((t: any) => t.name || t) : [],
                featured_image_id: article.featured_image_id,
                featured_image_url: article.featured_image?.thumbnail_url || article.featured_image_url,
                featured_image_title: article.featured_image_title || article.featured_image?.title || '',
                featured_image_alt: article.featured_image_alt || article.featured_image?.alt_text || '',
                featured_image_caption: article.featured_image_caption || article.featured_image?.caption || '',
                seo_focus_keyword: article.seo_focus_keyword || '',
                seo_title: article.seo_title || '',
                seo_description: article.seo_description || '',
                status: article.status || 'Draft'
            };
        } catch (error) {
            console.error('Failed to load article', error);
            alertService.errorToast('Failed to load article data.');
        } finally {
            isLoading.value = false;
        }
    };

    const submit = async (status: string = 'Draft') => {
        validationErrors.value = {}; 
        
        // Validation logic
        if (!form.value.title) validationErrors.value.title = ['The title field is required.'];
        if (!form.value.slug) validationErrors.value.slug = ['The slug field is required.'];
        if (!form.value.content) validationErrors.value.content = ['The content field is required.'];
        if (!form.value.excerpt) validationErrors.value.excerpt = ['The excerpt field is required.'];
        if (!form.value.category_id) validationErrors.value.category_id = ['Please select a category.'];
        if (!form.value.featured_image_id) validationErrors.value.featured_image_id = ['A cover image is required.'];
        if (!form.value.tags || form.value.tags.length === 0) validationErrors.value.tags = ['At least one tag is required.'];
        
        if (!form.value.seo_title) validationErrors.value.seo_title = ['SEO Title is required.'];
        if (!form.value.seo_description) validationErrors.value.seo_description = ['SEO Meta Description is required.'];
        if (!form.value.seo_focus_keyword) validationErrors.value.seo_focus_keyword = ['Focus Keyword is required.'];

        if (Object.keys(validationErrors.value).length > 0) {
            alertService.errorToast('Please fix the validation errors.');
            return;
        }

        form.value.status = status.toLowerCase();
        isLoading.value = true;
        
        try {
            let response;
            if (isEditMode.value && currentArticleId.value) {
                response = await articleService.update(currentArticleId.value, form.value);
            } else {
                response = await articleService.store(form.value);
            }

            if (response.success || response) {
                alertService.successToast(`Article ${isEditMode.value ? 'updated' : (status === 'Published' ? 'published' : 'saved as draft')} successfully.`);
                router.push({ name: 'articles.index' });
            }
        } catch (error: any) {
            if (error.response?.status === 422) {
                validationErrors.value = error.response.data.errors;
                alertService.errorToast('Validation failed. Please check the form.');
            } else {
                alertService.errorToast('An unexpected error occurred.');
            }
            console.error(error);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        form,
        isLoading,
        isEditMode,
        seoAnalysis,
        categoryOptions,
        tagSuggestions,
        isLoadingCategories,
        validationErrors,
        fetchCategories,
        fetchTags,
        generateSlug,
        loadArticle,
        submit
    };
}
