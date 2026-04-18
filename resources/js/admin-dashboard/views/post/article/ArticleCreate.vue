<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useArticleForm } from '../../../composables/useArticleForm';

// Layout Helpers
import BasePanel from '../../../components/ui/BasePanel.vue';
import BaseButton from '../../../components/ui/BaseButton.vue';
import BaseInput from '../../../components/ui/BaseInput.vue';
import BaseSelect from '../../../components/ui/BaseSelect.vue';
import BaseTagsInput from '../../../components/ui/BaseTagsInput.vue';
import BasePageHeader from '../../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../../components/ui/BasePageContainer.vue';
import BaseEditor from '../../../components/ui/BaseEditor.vue';
import MediaLibraryModal from '../../../components/article/MediaLibraryModal.vue';
import { ref } from 'vue';

const router = useRouter();
const {
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
} = useArticleForm();

const showMediaLibrary = ref(false);
const mediaInsertTarget = ref<'editor' | 'featured'>('editor');
const mediaInsertCallback = ref<((url: string, alt: string) => void) | null>(null);

const handleOpenMediaLibrary = (callback: (url: string, alt: string) => void) => {
    mediaInsertTarget.value = 'editor';
    mediaInsertCallback.value = callback;
    showMediaLibrary.value = true;
};

const handleOpenFeaturedMedia = () => {
    mediaInsertTarget.value = 'featured';
    showMediaLibrary.value = true;
};

const handleMediaSelect = (url: string, alt: string) => {
    if (mediaInsertTarget.value === 'editor' && mediaInsertCallback.value) {
        mediaInsertCallback.value(url, alt);
    } else if (mediaInsertTarget.value === 'featured') {
        form.value.featured_image_url = url;
        // You might also want to store the ID if your backend needs it
    }
};

onMounted(() => {
    fetchCategories();
    fetchTags();
});

// Auto-generate slug from title
watch(() => form.value.title, () => {
    generateSlug();
});
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Create New Article" subtitle="Follow the vertical steps to write and publish" back-label="Back to Articles" back-route-name="articles.index" />

        <div class="space-y-8 pb-20">
            <!-- Step 1: Content -->
            <BasePanel title="1. Article Content" icon="edit_note" no-overflow>
                <div class="space-y-6 py-2">
                    <BaseInput label="Title" icon="title" v-model="form.title" placeholder="Enter a catchy title..." />
                    
                    <div class="flex items-center gap-2 px-1 text-[11px] font-bold text-slate-400">
                        <span class="material-symbols-outlined text-sm">link</span>
                        <span>Permalink:</span>
                        <span class="text-primary truncate">{{ form.slug || 'your-article-slug' }}</span>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Body Content</label>
                        <BaseEditor 
                            v-model="form.content" 
                            placeholder="Start writing your amazing article here..."
                            @open-media-library="handleOpenMediaLibrary"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Excerpt (Short Summary)</label>
                        <textarea v-model="form.excerpt" rows="3" 
                            placeholder="Write a brief summary for list views..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none text-sm"></textarea>
                    </div>
                </div>
            </BasePanel>

            <!-- Step 2: Classification -->
            <BasePanel title="2. Classification" icon="category" no-overflow>
                <div class="space-y-6 py-2">
                    <BaseSelect 
                        label="Category" 
                        v-model="form.category_id" 
                        :options="categoryOptions"
                        :placeholder="isLoadingCategories ? 'Loading...' : 'Select Category from Article Group'"
                    />
                    
                    <BaseTagsInput 
                        label="Article Tags" 
                        v-model="form.tags" 
                        :suggestions="tagSuggestions"
                        placeholder="Add tags and press Enter..."
                    />
                </div>
            </BasePanel>

            <!-- Step 3: Media & SEO -->
            <BasePanel title="3. Media & SEO Information" icon="public" no-overflow>
                <div class="space-y-6 py-2">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Featured Image</label>
                        <div 
                            @click="handleOpenFeaturedMedia"
                            class="relative group aspect-video rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center hover:border-primary/50 transition-all cursor-pointer"
                        >
                            <img v-if="form.featured_image_url" :src="form.featured_image_url" class="w-full h-full object-cover">
                            <div v-else class="text-center p-4">
                                <span class="material-symbols-outlined text-slate-300 text-4xl">add_photo_alternate</span>
                                <p class="text-[10px] font-black text-slate-400 uppercase mt-1">Select from Media Library</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center mt-2">Recommended ratio: 16:9</p>
                    </div>

                    <hr class="border-slate-100">

                    <BaseInput label="SEO Title" icon="search" v-model="form.seo_title" placeholder="SEO optimized title..." />
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">SEO Description</label>
                        <textarea v-model="form.seo_description" rows="4" 
                            placeholder="Meta description for search engines..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none text-xs"></textarea>
                    </div>
                </div>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 p-2">
                        <div class="flex-1 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ready to save as draft or publish</span>
                        </div>
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="submit('Draft')" :loading="isLoading">
                            Save as Draft
                        </BaseButton>
                        <BaseButton icon="rocket_launch" class="w-full sm:w-auto" @click="submit('Published')" :loading="isLoading">
                            Publish Now
                        </BaseButton>
                    </div>
                </template>
            </BasePanel>
        </div>
    </BasePageContainer>

    <MediaLibraryModal 
        :show="showMediaLibrary" 
        @close="showMediaLibrary = false" 
        @select="handleMediaSelect" 
    />
</template>
