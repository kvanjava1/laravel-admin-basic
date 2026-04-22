<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useMediaForm } from '../../composables/useMediaForm';
import { useMediaMetadataOptions } from '../../composables/useMediaMetadataOptions';

// UI Components
import BasePanel from '../ui/BasePanel.vue';
import BaseButton from '../ui/BaseButton.vue';
import BaseInput from '../ui/BaseInput.vue';
import BaseSelect from '../ui/BaseSelect.vue';
import BaseTagsInput from '../ui/BaseTagsInput.vue';
import BaseAvatarUpload from '../ui/BaseAvatarUpload.vue';
import MediaImageCropper from '../ui/MediaImageCropper.vue';

const props = defineProps<{
    isModal?: boolean;
}>();

const emit = defineEmits(['success']);

const {
    imageSrc,
    preview169,
    preview43,
    showCropper,
    activeCropTarget,
    currentCropCoordinates,
    hasCrop169,
    hasCrop43,
    isLoading,
    title,
    altText,
    caption,
    description,
    categoryId,
    tags,
    handleImageSelect,
    openCropper,
    handleCropApply,
    submit,
    validationErrors,
} = useMediaForm({ redirect: !props.isModal });

const {
    categoryOptions,
    isLoadingCategories,
    hasImageCategoryGroup,
    tagSuggestions,
    fetchImageCategories,
    fetchTagSuggestions,
} = useMediaMetadataOptions();

const cropAspectRatio = computed(() => activeCropTarget.value === '16:9' ? 16 / 9 : 4 / 3);

onMounted(() => {
    fetchImageCategories();
    fetchTagSuggestions();
});

const handleSuccess = async () => {
    // Modify the submit to emit instead of redirect if in modal
    if (props.isModal) {
        isLoading.value = true;
        try {
            // We need to pass the success callback to the service or handle it here
            // For now, let's assume we want to get the uploaded item back
            const response = await submit();
            if (response) {
                emit('success', response);
            }
        } finally {
            isLoading.value = false;
        }
    } else {
        await submit();
    }
};
</script>

<template>
    <div class="space-y-8" :class="{ 'pb-12': !isModal }">
        <!-- Box 1: Source Image Selection -->
        <BasePanel title="1. Source Image" icon="add_photo_alternate" no-overflow>
            <div class="flex items-center gap-6 p-2">
                <div class="shrink-0">
                    <BaseAvatarUpload :model-value="imageSrc || undefined" @select="handleImageSelect" />
                </div>
                <div class="flex-1">
                    <div v-if="!imageSrc">
                        <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-tight">No image selected</h4>
                        <p class="text-xs text-slate-400 font-medium">Click the box to select your source image.</p>
                    </div>
                    <div v-else class="flex flex-col gap-1">
                        <div class="flex items-center gap-2 text-green-600">
                            <span class="material-symbols-outlined text-[18px] font-bold">check_circle</span>
                            <span class="text-xs font-black uppercase tracking-widest leading-none">Source Image Loaded</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium italic">Click image to change source.</p>
                    </div>
                </div>
            </div>
        </BasePanel>

        <!-- Box 2: 16:9 Preview -->
        <BasePanel v-if="imageSrc" title="2. Main Crop (16:9)" icon="crop_16_9" no-overflow>
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Standard: 16:9</p>
                        <h4 class="text-sm font-bold text-slate-800">Headline / Hero</h4>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest"
                        :class="hasCrop169 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        {{ hasCrop169 ? 'Done' : 'Pending' }}
                    </span>
                </div>

                <div @click="openCropper('16:9')" 
                    class="aspect-video rounded-2xl border-2 overflow-hidden cursor-pointer transition-all flex items-center justify-center bg-slate-50 group shadow-sm hover:shadow-md"
                    :class="preview169 ? 'border-slate-200 hover:border-primary' : 'border-primary border-dashed bg-primary/5'">
                    <img v-if="preview169" :src="preview169" class="w-full h-full object-cover">
                    <div v-else class="text-center">
                        <span class="material-symbols-outlined text-primary text-5xl mb-2">crop</span>
                        <p class="text-xs font-black text-primary uppercase">Start 16:9 Crop</p>
                    </div>
                </div>
            </div>
        </BasePanel>

        <!-- Box 3: 4:3 Preview -->
        <BasePanel v-if="imageSrc" title="3. Thumbnail Crop (4:3)" icon="crop_landscape" no-overflow>
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Standard: 4:3</p>
                        <h4 class="text-sm font-bold text-slate-800">Card Thumbnail</h4>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest"
                        :class="hasCrop43 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        {{ hasCrop43 ? 'Done' : 'Pending' }}
                    </span>
                </div>

                <div @click="openCropper('4:3')" 
                    class="aspect-[4/3] rounded-2xl border-2 overflow-hidden cursor-pointer transition-all flex items-center justify-center bg-slate-50 group shadow-sm hover:shadow-md"
                    :class="preview43 ? 'border-slate-200 hover:border-primary' : 'border-primary border-dashed bg-primary/5'">
                    <img v-if="preview43" :src="preview43" class="w-full h-full object-cover">
                    <div v-else class="text-center">
                        <span class="material-symbols-outlined text-primary text-5xl mb-2">crop_square</span>
                        <p class="text-xs font-black text-primary uppercase">Start 4:3 Crop</p>
                    </div>
                </div>
            </div>
        </BasePanel>

        <!-- Box 4: SEO Form -->
        <BasePanel v-if="imageSrc" title="4. SEO Information" icon="edit_note" no-overflow>
            <div class="space-y-6 py-2">
                <BaseSelect
                    label="Media Category"
                    v-model="categoryId"
                    :options="categoryOptions"
                    :placeholder="isLoadingCategories ? 'Loading...' : 'Select Category'"
                    :error="validationErrors.category_id?.[0]"
                />

                <BaseTagsInput
                    label="Media Tags"
                    v-model="tags"
                    :suggestions="tagSuggestions"
                    placeholder="Add tags..."
                />

                <BaseInput 
                    label="Image Title" 
                    icon="title" 
                    v-model="title" 
                    placeholder="e.g. Blue Nike Pro Running Shoes" 
                    :error="validationErrors.title?.[0]"
                />
                
                <BaseInput
                    label="Alt Text (Accessibility)"
                    type="textarea"
                    v-model="altText"
                    placeholder="Describe the image content..."
                    :error="validationErrors.alt_text?.[0]"
                />

                <BaseInput 
                    label="Caption" 
                    icon="subtitles" 
                    v-model="caption" 
                    placeholder="Optional caption" 
                    :error="validationErrors.caption?.[0]"
                />
            </div>

            <template #footer>
                <div class="flex flex-col sm:flex-row justify-end gap-3 p-2">
                    <BaseButton variant="ghost" class="w-full sm:w-auto" @click="handleSuccess">
                        {{ isModal ? 'Save & Insert' : 'Save & Publish' }}
                    </BaseButton>
                </div>
            </template>
        </BasePanel>
    </div>

    <MediaImageCropper 
        :show="showCropper" 
        :image="imageSrc || ''" 
        :aspect-ratio="cropAspectRatio" 
        :initial-coords="currentCropCoordinates"
        @close="showCropper = false" 
        @apply="handleCropApply" 
    />
</template>
