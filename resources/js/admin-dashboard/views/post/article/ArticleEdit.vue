<script setup lang="ts">
import { onMounted, watch, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
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
import BaseLabel from '../../../components/ui/BaseLabel.vue';
import MediaLibraryModal from '../../../components/article/MediaLibraryModal.vue';
import { alertService, Swal, commonConfig, colors } from '../../../utils/sweetalert';

const router = useRouter();
const route = useRoute();
const {
    form,
    isLoading,
    isEditMode,
    categoryOptions,
    tagSuggestions,
    seoAnalysis,
    isLoadingCategories,
    validationErrors,
    fetchCategories,
    fetchTags,
    generateSlug,
    loadArticle,
    submit
} = useArticleForm();

const fillExampleData = () => {
    form.value.title = 'The Future of Sustainable Cities in 2026';
    form.value.slug = 'future-sustainable-cities-2026';
    form.value.excerpt = 'Discover how innovative urban planning and green technologies are transforming our cities into carbon-neutral ecosystems by 2026.';
    form.value.content = `<h2>The Rise of Eco-Friendly Urbanism</h2><p>As we move into 2026, the concept of a "smart city" is evolving into a "sustainable city." Urban planners are now prioritizing green spaces, renewable energy integration, and efficient public transport over traditional high-rise density.</p><h3>Key Technologies Shaping Our Future</h3><p>From transparent solar panels to vertical forests, the materials we use are becoming as alive as the people who inhabit them. Sustainable architecture is no longer just about reducing harm—it's about actively healing the environment.</p><blockquote>"The cities of tomorrow are being built with the intelligence of nature."</blockquote><p>By implementing these strategies, we can ensure that our urban environments remain habitable and thriving for generations to come.</p>`;
    form.value.seo_title = 'Sustainable City Trends 2026 | Future of Green Urbanism';
    form.value.seo_description = 'Learn how green technologies and innovative urban planning are shaping sustainable cities in 2026. A comprehensive guide to the future of eco-friendly living.';
    form.value.seo_focus_keyword = 'Sustainable Cities';
    
    if (categoryOptions.value.length > 0) {
        form.value.category_id = categoryOptions.value[0].value;
    }
    
    form.value.tags = ['Sustainability', 'Urban Planning', 'Green Tech', '2026 Trends'];
    
    alertService.successToast('Example data has been filled!');
};

const showHelp = () => {
    Swal.fire({
        ...commonConfig,
        title: 'Google SEO Standards Guide',
        html: `
            <div class="text-left space-y-8 text-slate-600 pb-4">
                <p class="text-base border-b border-slate-100 pb-4">Optimize your content for the latest Google search algorithms (EEAT focus).</p>
                
                <div class="space-y-6">
                    <div>
                        <h4 class="text-sm font-black uppercase text-slate-900 tracking-widest flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                            Title & Headings
                        </h4>
                        <p class="text-base mt-2 pl-4 border-l-2 border-slate-200">Ensure only one H1 exists (the main title). Use H2 and H3 to create a logical content hierarchy. Keep titles between 50-60 characters.</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-black uppercase text-slate-900 tracking-widest flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                            Meta Information
                        </h4>
                        <p class="text-base mt-2 pl-4 border-l-2 border-slate-200">The meta description should be under 160 characters. It must summarize the article and include your focus keyword to improve Click-Through Rate (CTR).</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-black uppercase text-slate-900 tracking-widest flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                            Media Optimization
                        </h4>
                        <p class="text-base mt-2 pl-4 border-l-2 border-slate-200">Always provide descriptive Alt Text for images. This helps Google Image Search index your content and improves accessibility for screen readers.</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-black uppercase text-slate-900 tracking-widest flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                            E-E-A-T Content
                        </h4>
                        <p class="text-base mt-2 pl-4 border-l-2 border-slate-200">Focus on Experience, Expertise, Authoritativeness, and Trustworthiness. Write original content that provides real value to human readers, not just search engines.</p>
                    </div>
                </div>
            </div>
        `,
        icon: 'info',
        width: '800px',
        showDenyButton: true,
        confirmButtonText: 'Got it, thanks!',
        denyButtonText: 'Fill Example Data',
        confirmButtonColor: colors.primary,
        denyButtonColor: colors.success,
    }).then((result: any) => {
        if (result.isDenied) {
            fillExampleData();
        }
    });
};

const showMediaLibrary = ref(false);
const mediaInsertTarget = ref<'editor' | 'featured'>('editor');
const mediaInsertCallback = ref<((url: string, item: any) => void) | null>(null);

const handleOpenMediaLibrary = (callback: (url: string, item: any) => void) => {
    mediaInsertTarget.value = 'editor';
    mediaInsertCallback.value = callback;
    showMediaLibrary.value = true;
};

const handleOpenFeaturedMedia = () => {
    mediaInsertTarget.value = 'featured';
    showMediaLibrary.value = true;
};

const handleMediaSelect = (url: string, item: any) => {
    if (mediaInsertTarget.value === 'editor' && mediaInsertCallback.value) {
        mediaInsertCallback.value(url, item);
    } else if (mediaInsertTarget.value === 'featured') {
        form.value.featured_image_url = url;
        form.value.featured_image_id = item.id;
        form.value.featured_image_title = item.title || '';
        form.value.featured_image_alt = item.alt_text || '';
        form.value.featured_image_caption = item.caption || '';
    }
};

onMounted(async () => {
    await fetchCategories();
    await fetchTags();
    
    const id = route.params.id;
    if (id) {
        loadArticle(Number(id));
    }
});

// Auto-generate slug from title (only if not in edit mode or explicitly wanted)
watch(() => form.value.title, () => {
    if (!isEditMode.value) {
        generateSlug();
    }
});
</script>

<template>
    <BasePageContainer variant="wide">
        <BasePageHeader title="Edit Article" subtitle="Update your content and refine SEO" back-label="Back to Articles" back-route-name="articles.index" />

        <div v-if="isLoading && !form.title" class="flex flex-col items-center justify-center py-40 animate-pulse">
            <span class="material-symbols-outlined text-6xl text-slate-200">article</span>
            <p class="text-slate-400 font-black uppercase tracking-widest mt-4">Loading Article Data...</p>
        </div>

        <div v-else class="grid grid-cols-12 gap-8 pb-20">
            <!-- Main Content Area (Left) -->
            <div class="col-span-12 lg:col-span-8 space-y-8">
                <!-- Content Panel -->
                <BasePanel title="Article Content" no-overflow>
                    <div class="space-y-6 py-4">
                        <!-- Premium H1-Style Title -->
                        <div class="px-2">
                            <input 
                                type="text"
                                v-model="form.title"
                                placeholder="Enter Article Title..."
                                class="w-full text-4xl font-black text-slate-900 placeholder:text-slate-200 border-none focus:ring-0 p-0 bg-transparent"
                            />
                            <div v-if="validationErrors.title" class="text-rose-500 text-sm font-bold mt-2 animate-pulse flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ validationErrors.title[0] }}
                            </div>

                            <!-- Permalink inline -->
                            <div class="flex items-center gap-2 mt-4 text-[11px] font-bold text-slate-400 bg-slate-50 w-fit px-3 py-1.5 rounded-full border border-slate-100">
                                <span class="material-symbols-outlined text-sm">link</span>
                                <span>URL:</span>
                                <span class="text-primary truncate">{{ form.slug || 'your-article-slug' }}</span>
                            </div>
                        </div>

                        <hr class="border-slate-100 mx-2" />

                        <div class="space-y-2">
                            <BaseLabel value="Short Summary (Lead)" />
                            <BaseEditor 
                                v-model="form.excerpt" 
                                placeholder="Write a catchy lead or summary here..."
                                minimal
                                :error="validationErrors.excerpt?.[0]"
                            />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-2">
                                <BaseLabel value="Body Content" class="!mb-0" />
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Story Mode</span>
                            </div>
                            <BaseEditor 
                                v-model="form.content" 
                                placeholder="Start writing your amazing article here..."
                                @open-media-library="handleOpenMediaLibrary"
                                :error="validationErrors.content?.[0]"
                            />
                        </div>
                    </div>
                </BasePanel>

                <!-- SEO Information (Below Content) -->
                <BasePanel title="Search Engine Optimization" icon="public" no-overflow>
                    <div class="space-y-6 py-2">
                        <BaseInput label="SEO Title" icon="search" v-model="form.seo_title" placeholder="SEO optimized title..." :error="validationErrors.seo_title?.[0]" />
                        
                        <div class="space-y-2">
                            <BaseLabel value="SEO Description" />
                            <textarea v-model="form.seo_description" rows="3" 
                                placeholder="Meta description for search engines..."
                                :class="[
                                    validationErrors.seo_description ? 'border-rose-400 bg-rose-50/30 ring-2 ring-rose-50' : 'border-slate-200 bg-slate-50 focus:ring-primary/20 focus:border-primary'
                                ]"
                                class="w-full px-4 py-3 border rounded-2xl text-slate-700 focus:outline-none focus:ring-2 transition-all resize-none text-base"></textarea>
                        </div>

                        <div class="space-y-6">
                            <BaseInput 
                                label="Focus Keyword" 
                                v-model="form.seo_focus_keyword" 
                                placeholder="e.g. Electric Car 2026" 
                                icon="key"
                                :error="validationErrors.seo_focus_keyword?.[0]"
                            />
                            
                            <div v-if="seoAnalysis" class="flex flex-col gap-4 pt-6 border-t border-slate-200/50 mt-4">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]" :class="seoAnalysis.inTitle ? 'text-emerald-500' : 'text-rose-500'">{{ seoAnalysis.inTitle ? 'check_circle' : 'cancel' }}</span>
                                    <div>
                                        <p class="text-xs font-bold" :class="seoAnalysis.inTitle ? 'text-emerald-700' : 'text-rose-700'">Keyword in Title</p>
                                        <p class="text-[11px] text-slate-400 italic">
                                            {{ seoAnalysis.inTitle ? 'Focus keyword found in the article title.' : 'Focus keyword NOT found in the article title.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]" :class="seoAnalysis.inSlug ? 'text-emerald-500' : 'text-rose-500'">{{ seoAnalysis.inSlug ? 'check_circle' : 'cancel' }}</span>
                                    <div>
                                        <p class="text-xs font-bold" :class="seoAnalysis.inSlug ? 'text-emerald-700' : 'text-rose-700'">Keyword in Slug</p>
                                        <p class="text-[11px] text-slate-400 italic">
                                            {{ seoAnalysis.inSlug ? 'Focus keyword found in the URL permalink.' : 'Focus keyword NOT found in the URL permalink.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]" :class="seoAnalysis.inDescription ? 'text-emerald-500' : 'text-rose-500'">{{ seoAnalysis.inDescription ? 'check_circle' : 'cancel' }}</span>
                                    <div>
                                        <p class="text-xs font-bold" :class="seoAnalysis.inDescription ? 'text-emerald-700' : 'text-rose-700'">Keyword in Meta Description</p>
                                        <p class="text-[11px] text-slate-400 italic">
                                            {{ seoAnalysis.inDescription ? 'Focus keyword found in the meta description.' : 'Focus keyword NOT found in the meta description.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]" :class="seoAnalysis.inContent ? 'text-emerald-500' : 'text-rose-500'">{{ seoAnalysis.inContent ? 'check_circle' : 'cancel' }}</span>
                                    <div>
                                        <p class="text-xs font-bold" :class="seoAnalysis.inContent ? 'text-emerald-700' : 'text-rose-700'">Keyword in Content</p>
                                        <p class="text-[11px] text-slate-400 italic">
                                            {{ seoAnalysis.inContent ? 'Focus keyword found in the main article body.' : 'Focus keyword NOT found in the main article body.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </BasePanel>
            </div>

            <!-- Sidebar (Right) -->
            <div class="col-span-12 lg:col-span-4 space-y-8">
                <!-- Classification -->
                <BasePanel title="Classification" icon="category">
                    <div class="space-y-6">
                        <BaseSelect 
                            label="Category" 
                            v-model="form.category_id" 
                            :options="categoryOptions"
                            :placeholder="isLoadingCategories ? 'Loading...' : 'Select Category'"
                            :error="validationErrors.category_id?.[0]"
                        />
                        
                        <BaseTagsInput 
                            label="Tags" 
                            v-model="form.tags" 
                            :suggestions="tagSuggestions"
                            placeholder="Add tags..."
                            :error="validationErrors.tags?.[0]"
                        />
                    </div>
                </BasePanel>

                <!-- Featured Media -->
                <BasePanel title="Featured Image" icon="image">
                    <div class="space-y-4">
                        <div 
                            @click="handleOpenFeaturedMedia"
                            :class="[
                                validationErrors.featured_image_id ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200 bg-slate-50 hover:border-primary/50'
                            ]"
                            class="relative group aspect-video rounded-2xl border-2 border-dashed overflow-hidden flex items-center justify-center transition-all cursor-pointer"
                        >
                            <img v-if="form.featured_image_url" :src="form.featured_image_url" class="w-full h-full object-cover">
                            <div v-else class="text-center p-4">
                                <span class="material-symbols-outlined text-slate-300 text-4xl" :class="{ 'text-rose-400': validationErrors.featured_image_id }">add_photo_alternate</span>
                                <p class="text-[10px] font-black text-slate-400 uppercase mt-1">
                                    Click to select image
                                </p>
                            </div>
                            <!-- Overlay on hover -->
                            <div v-if="form.featured_image_url" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <span class="text-white text-xs font-bold bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-full">Change Image</span>
                            </div>
                        </div>

                        <div v-if="form.featured_image_url" class="space-y-4 animate-in fade-in slide-in-from-right-4 duration-300">
                            <BaseInput label="Image Title" v-model="form.featured_image_title" placeholder="SEO Title..." icon="title" />
                            <BaseInput label="Alt Text" v-model="form.featured_image_alt" placeholder="Describe for SEO..." icon="accessibility_new" />
                            <BaseInput label="Image Caption" v-model="form.featured_image_caption" placeholder="Visible caption..." icon="notes" />
                        </div>
                    </div>
                </BasePanel>

                <!-- Publishing Panel -->
                <BasePanel title="Publishing" icon="rocket_launch">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="h-10 w-10 rounded-full bg-slate-900 flex items-center justify-center text-white">
                                <span class="material-symbols-outlined">edit_notifications</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Current Status</p>
                                <p class="text-sm font-bold text-slate-700 mt-1 capitalize">{{ form.status }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <BaseButton icon="save" class="w-full justify-center py-4" @click="submit(form.status)" :loading="isLoading">
                                Update Article
                            </BaseButton>
                            <BaseButton v-if="form.status === 'draft'" icon="rocket_launch" variant="ghost" class="w-full justify-center py-4" @click="submit('published')" :loading="isLoading">
                                Publish Now
                            </BaseButton>
                            <div class="pt-2 border-t border-slate-100 mt-2">
                                <button @click="showHelp" type="button" class="w-full flex items-center justify-center gap-2 py-2 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                    Writing Guide
                                </button>
                            </div>
                        </div>
                    </div>
                </BasePanel>
            </div>
        </div>
    </BasePageContainer>

    <MediaLibraryModal 
        :show="showMediaLibrary" 
        @close="showMediaLibrary = false" 
        @select="handleMediaSelect" 
    />
</template>
