<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { mediaService } from '../../services/mediaService';
import { useMediaMetadataOptions } from '../../composables/useMediaMetadataOptions';

import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';
import BaseTagsInput from '../../components/ui/BaseTagsInput.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import MediaImageCropper from '../../components/ui/MediaImageCropper.vue';
import { alertService } from '../../utils/sweetalert';

type CropRatio = '16:9' | '4:3';

interface CropCoordinates {
    x: number;
    y: number;
    width: number;
    height: number;
}

const router = useRouter();
const route = useRoute();

const mediaId = computed(() => Number(route.params.id));
const showCropper = ref(false);
const activeCropTarget = ref<CropRatio>('16:9');
const imageSrc = ref<string | null>(null);
const preview169 = ref<string | null>(null);
const preview43 = ref<string | null>(null);
const isLoading = ref(false);
const isLoadingMedia = ref(false);

const title = ref('');
const altText = ref('');
const caption = ref('');
const description = ref('');
const categoryId = ref<string>('');
const tags = ref<string[]>([]);
const crop169 = ref<CropCoordinates | null>(null);
const crop43 = ref<CropCoordinates | null>(null);

const {
    categoryOptions,
    isLoadingCategories,
    hasImageCategoryGroup,
    tagSuggestions,
    fetchImageCategories,
    fetchTagSuggestions,
} = useMediaMetadataOptions();

const currentCropCoordinates = computed(() => activeCropTarget.value === '16:9' ? crop169.value : crop43.value);
const hasCrop169 = computed(() => crop169.value !== null);
const hasCrop43 = computed(() => crop43.value !== null);
const cropAspectRatio = computed(() => activeCropTarget.value === '16:9' ? 16 / 9 : 4 / 3);

const loadMedia = async () => {
    isLoadingMedia.value = true;

    try {
        const response = await mediaService.show(mediaId.value);
        const media = response.data;

        title.value = media.title;
        altText.value = media.alt_text;
        caption.value = media.caption || '';
        description.value = media.description || '';
        categoryId.value = media.category_id ? media.category_id.toString() : '';
        tags.value = (media.tags || []).map((tag: { name: string }) => tag.name);
        imageSrc.value = media.original_url;
        preview169.value = media.ratio_16_9_big_url || media.ratio_16_9_medium_url || media.original_url;
        preview43.value = media.ratio_4_3_big_url || media.ratio_4_3_medium_url || media.original_url;
        crop169.value = {
            x: media.crop_16_9_x,
            y: media.crop_16_9_y,
            width: media.crop_16_9_width,
            height: media.crop_16_9_height,
        };
        crop43.value = {
            x: media.crop_4_3_x,
            y: media.crop_4_3_y,
            width: media.crop_4_3_width,
            height: media.crop_4_3_height,
        };
    } catch (error: any) {
        alertService.errorToast(
            error.response?.data?.message || 'Load Error',
            'Could not load media for editing.'
        );
        router.push({ name: 'media.index' });
    } finally {
        isLoadingMedia.value = false;
    }
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
};

const validate = () => {
    if (!title.value || !altText.value) {
        alertService.errorToast('Validation Error', 'Title and alt text are required.');
        return false;
    }

    if (!crop169.value || !crop43.value) {
        alertService.errorToast('Validation Error', 'Please complete both crops before saving.');
        return false;
    }

    return true;
};

const submit = async () => {
    if (!validate()) {
        return;
    }

    isLoading.value = true;

    try {
        await mediaService.update(mediaId.value, {
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

        alertService.successToast('Media updated successfully.');
        router.push({ name: 'media.index' });
    } catch (error: any) {
        const response = error.response?.data;
        alertService.errorToast(
            response?.message || error.message || 'Update Failed',
            response?.errors
                ? Object.values(response.errors).flat().join(' ')
                : 'Something went wrong while updating the media item.'
        );
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    await Promise.all([
        fetchImageCategories(),
        fetchTagSuggestions(),
        loadMedia(),
    ]);
});
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Edit Media" subtitle="Update crop, category, tags, and SEO metadata" back-label="Back to Library" back-route-name="media.index" />

        <div v-if="isLoadingMedia" class="py-24 text-center">
            <span class="material-symbols-outlined animate-spin text-4xl text-primary">sync</span>
            <p class="mt-3 text-sm font-medium text-slate-500">Loading media editor...</p>
        </div>

        <div v-else class="space-y-8 pb-12">
            <BasePanel title="1. Main Crop (16:9)" icon="crop_16_9">
                <div class="space-y-4">
                    <div class="rounded-2xl border px-4 py-4 transition-all" :class="hasCrop169 ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-200 bg-white'">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">16:9</p>
                                <h4 class="mt-1 text-sm font-bold text-slate-800">Main Crop</h4>
                                <p class="mt-1 text-xs text-slate-500">Untuk headline dan Google Discover.</p>
                            </div>
                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em]"
                                :class="hasCrop169 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'"
                            >
                                {{ hasCrop169 ? 'Ready' : 'Pending' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="aspect-video rounded-2xl border-2 overflow-hidden cursor-pointer transition-all flex items-center justify-center bg-slate-50 group shadow-sm hover:shadow-md"
                        :class="preview169 ? 'border-slate-200 hover:border-primary' : 'border-primary border-dashed bg-primary/5'"
                        @click="openCropper('16:9')"
                    >
                        <img v-if="preview169" :src="preview169" class="w-full h-full object-cover">
                        <div v-else class="text-center">
                            <span class="material-symbols-outlined text-primary text-5xl mb-2">crop</span>
                            <p class="text-xs font-black text-primary uppercase">Start 16:9 Crop</p>
                        </div>
                    </div>
                </div>
            </BasePanel>

            <BasePanel title="2. Thumbnail Crop (4:3)" icon="crop_landscape">
                <div class="space-y-4">
                    <div class="rounded-2xl border px-4 py-4 transition-all" :class="hasCrop43 ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-200 bg-white'">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">4:3</p>
                                <h4 class="mt-1 text-sm font-bold text-slate-800">Thumbnail Crop</h4>
                                <p class="mt-1 text-xs text-slate-500">Untuk list artikel dan thumbnail.</p>
                            </div>
                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em]"
                                :class="hasCrop43 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'"
                            >
                                {{ hasCrop43 ? 'Ready' : 'Pending' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="aspect-[4/3] max-w-md mx-auto rounded-2xl border-2 overflow-hidden cursor-pointer transition-all flex items-center justify-center bg-slate-50 group shadow-sm hover:shadow-md"
                        :class="preview43 ? 'border-slate-200 hover:border-primary' : 'border-primary border-dashed bg-primary/5'"
                        @click="openCropper('4:3')"
                    >
                        <img v-if="preview43" :src="preview43" class="w-full h-full object-cover">
                        <div v-else class="text-center">
                            <span class="material-symbols-outlined text-primary text-5xl mb-2">crop_square</span>
                            <p class="text-xs font-black text-primary uppercase">Start 4:3 Crop</p>
                        </div>
                    </div>
                </div>
            </BasePanel>

            <BasePanel title="3. SEO Information" icon="edit_note">
                <div class="space-y-6 py-2">
                    <BaseSelect
                        label="Media Category"
                        v-model="categoryId"
                        :options="categoryOptions"
                        :placeholder="isLoadingCategories ? 'Loading image categories...' : 'Select category from Images group'"
                    />

                    <p v-if="!hasImageCategoryGroup" class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-600">
                        Images category group was not found. Category selection is temporarily unavailable.
                    </p>

                    <BaseTagsInput
                        label="Media Tags"
                        v-model="tags"
                        :suggestions="tagSuggestions"
                        placeholder="Type a tag and press Enter..."
                    />

                    <BaseInput label="Image Title" icon="title" v-model="title" placeholder="e.g. Blue Nike Pro Running Shoes" />

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Alt Text (Accessibility)</label>
                        <textarea
                            v-model="altText"
                            rows="4"
                            placeholder="Describe the image content for SEO..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none"
                        ></textarea>
                    </div>

                    <BaseInput label="Caption" icon="subtitles" v-model="caption" placeholder="Optional short caption for library display" />

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Description</label>
                        <textarea
                            v-model="description"
                            rows="5"
                            placeholder="Optional long-form description or editorial note..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none"
                        ></textarea>
                    </div>
                </div>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 p-2">
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="router.push({ name: 'media.index' })">
                            Cancel
                        </BaseButton>
                        <BaseButton icon="save" class="w-full sm:w-auto" @click="submit" size="lg">
                            {{ isLoading ? 'Saving...' : 'Save Changes' }}
                        </BaseButton>
                    </div>
                </template>
            </BasePanel>
        </div>
    </BasePageContainer>

    <MediaImageCropper
        :show="showCropper"
        :image="imageSrc || ''"
        :aspect-ratio="cropAspectRatio"
        :initial-coords="currentCropCoordinates"
        :aspect-ratio-label="activeCropTarget === '16:9' ? 'Main Crop (16:9)' : 'Thumbnail (4:3)'"
        @close="showCropper = false"
        @apply="handleCropApply"
    />
</template>
