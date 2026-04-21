import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { mediaService } from '../services/mediaService';
import { alertService } from '../utils/sweetalert';
import { useMediaMetadataOptions } from './useMediaMetadataOptions';

type CropRatio = '16:9' | '4:3';

interface CropCoordinates {
    x: number;
    y: number;
    width: number;
    height: number;
}

export function useMediaForm(options: { redirect?: boolean } = { redirect: true }) {
    const router = useRouter();

    const imageFile = ref<File | null>(null);
    const imageSrc = ref<string | null>(null);
    const preview169 = ref<string | null>(null);
    const preview43 = ref<string | null>(null);
    const showCropper = ref(false);
    const activeCropTarget = ref<CropRatio>('16:9');
    const isLoading = ref(false);
    const validationErrors = ref<Record<string, string[]>>({});

    const title = ref('');
    const altText = ref('');
    const caption = ref('');
    const description = ref('');
    const categoryId = ref<string>('');
    const tags = ref<string[]>([]);
    const {
        categoryOptions,
        isLoadingCategories,
        hasImageCategoryGroup,
        tagSuggestions,
        fetchImageCategories,
        fetchTagSuggestions,
    } = useMediaMetadataOptions();

    const crop169 = ref<CropCoordinates | null>(null);
    const crop43 = ref<CropCoordinates | null>(null);
    const currentCropCoordinates = computed(() => activeCropTarget.value === '16:9' ? crop169.value : crop43.value);
    const hasCrop169 = computed(() => crop169.value !== null);
    const hasCrop43 = computed(() => crop43.value !== null);
    const completedCropCount = computed(() => Number(hasCrop169.value) + Number(hasCrop43.value));
    const nextRequiredCrop = computed<CropRatio | null>(() => {
        if (!hasCrop169.value) {
            return '16:9';
        }

        if (!hasCrop43.value) {
            return '4:3';
        }

        return null;
    });

    const handleImageSelect = (data: { dataUrl: string; file: File }) => {
        imageFile.value = data.file;
        imageSrc.value = data.dataUrl;
        preview169.value = null;
        preview43.value = null;
        crop169.value = null;
        crop43.value = null;
        activeCropTarget.value = '16:9';
        alertService.infoToast('Step 1: Crop for headline (16:9)');
        showCropper.value = true;
    };

    const openCropper = (target: CropRatio) => {
        if (!imageSrc.value) {
            return;
        }

        activeCropTarget.value = target;
        alertService.infoToast(target === '16:9' ? 'Crop for headline (16:9)' : 'Crop for thumbnail (4:3)');
        showCropper.value = true;
    };

    const handleCropApply = (data: { dataUrl: string; coords: CropCoordinates }) => {
        if (activeCropTarget.value === '16:9') {
            preview169.value = data.dataUrl;
            crop169.value = data.coords;
        } else {
            preview43.value = data.dataUrl;
            crop43.value = data.coords;
        }

        showCropper.value = false;

        if (activeCropTarget.value === '16:9' && !crop43.value) {
            alertService.successToast('Main crop saved. Continue with thumbnail crop (4:3) when you are ready.');
            return;
        }

        if (activeCropTarget.value === '4:3' && crop169.value) {
            alertService.successToast('Thumbnail crop saved.');
        }
    };

    const validate = () => {
        validationErrors.value = {};
        let isValid = true;

        if (!imageFile.value) {
            alertService.errorToast('Validation Error', 'Please choose a source image.');
            isValid = false;
        }

        if (!title.value) {
            validationErrors.value.title = ['The title field is required.'];
            isValid = false;
        }

        if (!altText.value) {
            validationErrors.value.alt_text = ['The alt text field is required.'];
            isValid = false;
        }

        if (!crop169.value || !crop43.value) {
            alertService.errorToast('Validation Error', 'Please complete both crops before saving.');
            isValid = false;
        }

        return isValid;
    };

    const submit = async () => {
        if (!validate()) {
            return;
        }

        isLoading.value = true;
        validationErrors.value = {};

        try {
            const response = await mediaService.store({
                image: imageFile.value,
                title: title.value,
                alt_text: altText.value,
                caption: caption.value,
                description: description.value,
                category_id: categoryId.value || null,
                tags: tags.value,
                crop_16_9_x: crop169.value?.x,
                crop_16_9_y: crop169.value?.y,
                crop_16_9_width: crop169.value?.width,
                crop_16_9_height: crop169.value?.height,
                crop_4_3_x: crop43.value?.x,
                crop_4_3_y: crop43.value?.y,
                crop_4_3_width: crop43.value?.width,
                crop_4_3_height: crop43.value?.height,
            });

            alertService.successToast('Media uploaded successfully.');
            
            if (options.redirect) {
                router.push({ name: 'media.index' });
            }

            return response.data || response;
        } catch (err: any) {
            if (err.response?.status === 422) {
                validationErrors.value = err.response.data.errors;
            }
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                err.response?.status === 422 ? 'Please check the form for errors.' : 'Something went wrong while uploading the image.'
            );
        } finally {
            isLoading.value = false;
        }
    };

    return {
        imageSrc,
        preview169,
        preview43,
        showCropper,
        activeCropTarget,
        currentCropCoordinates,
        hasCrop169,
        hasCrop43,
        completedCropCount,
        nextRequiredCrop,
        isLoading,
        validationErrors,
        title,
        altText,
        caption,
        description,
        categoryId,
        tags,
        categoryOptions,
        tagSuggestions,
        isLoadingCategories,
        hasImageCategoryGroup,
        handleImageSelect,
        openCropper,
        handleCropApply,
        fetchImageCategories,
        fetchTagSuggestions,
        submit,
    };
}
