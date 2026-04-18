<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useMediaForm } from '../../composables/useMediaForm';

// Layout Helpers
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';
import BaseTagsInput from '../../components/ui/BaseTagsInput.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import BaseAvatarUpload from '../../components/ui/BaseAvatarUpload.vue';
import MediaImageCropper from '../../components/ui/MediaImageCropper.vue';

const router = useRouter();
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
} = useMediaForm();

const cropAspectRatio = computed(() => activeCropTarget.value === '16:9' ? 16 / 9 : 4 / 3);

onMounted(() => {
    fetchImageCategories();
    fetchTagSuggestions();
});
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Upload Media" subtitle="Vertical workflow for Hero, Thumbnail, and SEO" back-label="Back to Library" back-route-name="media.index" />

        <div class="space-y-8 pb-12">
            <!-- Box 1: Source Image Selection (Simplified) -->
            <BasePanel title="1. Source Image" icon="add_photo_alternate">
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
                            <p class="text-xs text-slate-500 font-medium italic">You can click the image to choose a different source.</p>
                        </div>
                    </div>
                </div>
            </BasePanel>

            <!-- Box 2: 16:9 Preview -->
            <BasePanel v-if="imageSrc" title="2. Main Crop (16:9)" icon="crop_16_9">
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
                                {{ hasCrop169 ? 'Done' : 'Pending' }}
                            </span>
                        </div>
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
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest text-center">Selesaikan crop ini dulu, lalu lanjut ke thumbnail jika diperlukan.</p>
                </div>
            </BasePanel>

            <!-- Box 3: 4:3 Preview -->
            <BasePanel v-if="imageSrc" title="3. Thumbnail Crop (4:3)" icon="crop_landscape">
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
                                {{ hasCrop43 ? 'Done' : 'Pending' }}
                            </span>
                        </div>
                    </div>

                    <div @click="openCropper('4:3')" 
                        class="aspect-[4/3] max-w-md mx-auto rounded-2xl border-2 overflow-hidden cursor-pointer transition-all flex items-center justify-center bg-slate-50 group shadow-sm hover:shadow-md"
                        :class="preview43 ? 'border-slate-200 hover:border-primary' : 'border-primary border-dashed bg-primary/5'">
                        <img v-if="preview43" :src="preview43" class="w-full h-full object-cover">
                        <div v-else class="text-center">
                            <span class="material-symbols-outlined text-primary text-5xl mb-2">crop_square</span>
                            <p class="text-xs font-black text-primary uppercase">Start 4:3 Crop</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest text-center">Crop ini opsional alurnya tetap manual. Tidak akan muncul otomatis.</p>
                </div>
            </BasePanel>

            <!-- Box 4: SEO Form -->
            <BasePanel v-if="imageSrc" title="4. SEO Information" icon="edit_note">
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
                        <textarea v-model="altText" rows="4" placeholder="Describe the image content for SEO..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none"></textarea>
                    </div>

                    <BaseInput label="Caption" icon="subtitles" v-model="caption" placeholder="Optional short caption for library display" />

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Description</label>
                        <textarea v-model="description" rows="5" placeholder="Optional long-form description or editorial note..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none"></textarea>
                    </div>
                </div>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 p-2">
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="router.push({ name: 'media.index' })">
                            Cancel
                        </BaseButton>
                        <BaseButton icon="cloud_upload" class="w-full sm:w-auto" @click="submit" size="lg">
                            {{ isLoading ? 'Uploading...' : 'Save & Publish' }}
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
