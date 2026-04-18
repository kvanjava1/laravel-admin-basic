import { ref } from 'vue';
import { categoryService, type Category, type CategoryGroup } from '../services/categoryService';
import { tagService } from '../services/tagService';

const mediaTagSuggestions = [
    'banner', 'hero', 'discover', 'article', 'news',
    'photography', 'urban', 'nature', 'portrait', 'premium',
    'landscape', 'minimalist', 'vibrant', 'corporate',
];

export function useMediaMetadataOptions() {
    const categoryOptions = ref<{ label: string; value: string; disabled?: boolean }[]>([]);
    const isLoadingCategories = ref(false);
    const hasImageCategoryGroup = ref(true);
    const tagSuggestions = ref<string[]>(mediaTagSuggestions);

    const flattenCategories = (
        categories: Category[],
        depth = 0
    ): { label: string; value: string; disabled?: boolean }[] => {
        let options: { label: string; value: string; disabled?: boolean }[] = [];

        categories.forEach((category) => {
            const hasChildren = Boolean(category.children_recursive?.length);

            options.push({
                label: depth > 0 ? `${'— '.repeat(depth)}${category.name}` : category.name,
                value: category.id.toString(),
                disabled: hasChildren,
            });

            if (hasChildren) {
                options = [
                    ...options,
                    ...flattenCategories(category.children_recursive || [], depth + 1),
                ];
            }
        });

        return options;
    };

    const resolveImageGroup = (groups: CategoryGroup[]): CategoryGroup | undefined => {
        return groups.find((group) => group.slug === 'images')
            || groups.find((group) => group.name.toLowerCase() === 'images')
            || groups.find((group) => group.slug.includes('image'))
            || groups.find((group) => group.name.toLowerCase().includes('image'));
    };

    const fetchImageCategories = async () => {
        isLoadingCategories.value = true;
        hasImageCategoryGroup.value = true;

        try {
            const groupResponse = await categoryService.getGroups();
            const groups = groupResponse?.data || [];
            const imageGroup = resolveImageGroup(groups);

            if (!imageGroup) {
                hasImageCategoryGroup.value = false;
                categoryOptions.value = [];
                return;
            }

            const categoryResponse = await categoryService.getCategories(imageGroup.id);
            const categories = categoryResponse?.data || [];
            categoryOptions.value = flattenCategories(categories);
        } catch (error) {
            console.error('Failed to load media categories:', error);
            hasImageCategoryGroup.value = false;
            categoryOptions.value = [];
        } finally {
            isLoadingCategories.value = false;
        }
    };

    const fetchTagSuggestions = async () => {
        try {
            const response = await tagService.getOptions({ limit: 100 });
            const databaseTags = (response?.data || []).map((tag: { name: string }) => tag.name);

            tagSuggestions.value = Array.from(new Set([
                ...databaseTags,
                ...mediaTagSuggestions,
            ]));
        } catch (error) {
            console.error('Failed to load tag suggestions:', error);
            tagSuggestions.value = mediaTagSuggestions;
        }
    };

    return {
        categoryOptions,
        isLoadingCategories,
        hasImageCategoryGroup,
        tagSuggestions,
        fetchImageCategories,
        fetchTagSuggestions,
    };
}
