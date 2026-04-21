<script setup lang="ts">
import { useRouter } from 'vue-router';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';
import ArticleStatusBadge from './ArticleStatusBadge.vue';

interface Props {
    show: boolean;
    article: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);
const router = useRouter();

const formatDate = (dateString: string) => {
    if (!dateString) return 'Not Published';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const handleEdit = () => {
    if (!props.article?.id) return;
    emit('close');
    router.push({ name: 'articles.edit', params: { id: props.article.id } });
};
</script>

<template>
    <BaseModal :show="show" title="Article Preview" icon="visibility" @close="emit('close')" size="4xl">
        <div v-if="article" class="space-y-10 pb-6">
            <!-- 1. Hero Section (Full Width Image) -->
            <div class="-mx-6 -mt-6 mb-10 overflow-hidden relative group">
                <div v-if="article.featured_image" class="bg-slate-100">
                    <img :src="article.featured_image.original_url" class="w-full aspect-[21/9] object-cover">
                    <!-- Editorial Caption Below Image -->
                    <div v-if="article.featured_image_caption" 
                        class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-slate-400">info</span>
                        <p class="text-[11px] text-slate-500 italic font-medium leading-none">
                            {{ article.featured_image_caption }}
                        </p>
                    </div>
                </div>
                <div v-else 
                    class="w-full h-40 bg-slate-50 flex items-center justify-center border-b border-dashed border-slate-200">
                    <span class="material-symbols-outlined text-slate-300 text-4xl">image</span>
                </div>
            </div>

            <!-- 2. Primary Header & Metadata -->
            <div class="px-2">
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <ArticleStatusBadge :status="article.status" />
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-black uppercase tracking-widest">
                        {{ article.category?.name || 'Uncategorized' }}
                    </span>
                    <span class="text-slate-300">|</span>
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        <span class="text-xs font-bold">{{ formatDate(article.published_at) }}</span>
                    </div>
                </div>

                <h1 class="text-4xl font-black text-slate-900 leading-[1.1] mb-2 tracking-tight">
                    {{ article.title }}
                </h1>
                
                <div class="flex items-center gap-2 mb-8">
                    <span class="material-symbols-outlined text-sm text-slate-300">link</span>
                    <span class="text-xs font-medium text-slate-400">/{{ article.slug }}</span>
                </div>

                <!-- Author Card (Silver Style) -->
                <div class="flex items-center gap-4 p-4 bg-[#f8f9fa] rounded-2xl border border-slate-200 w-fit">
                    <div class="h-12 w-12 rounded-full bg-slate-900 border-4 border-white shadow-sm flex items-center justify-center text-white font-black text-lg">
                        {{ article.author?.name?.charAt(0) || 'A' }}
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1 text-slate-400">Written By</p>
                        <p class="text-base font-bold text-slate-900 leading-none">{{ article.author?.name || 'Administrator' }}</p>
                    </div>
                </div>
            </div>

            <!-- 3. Content Area (Single Column) -->
            <div class="space-y-12 px-2">
                <div class="prose prose-slate max-w-none">
                    <!-- Excerpt -->
                    <div class="text-xl text-slate-600 font-medium leading-relaxed border-l-4 border-primary pl-8 mb-10 italic"
                        v-html="article.excerpt">
                    </div>
                    
                    <!-- Main Body -->
                    <div class="article-body-content text-slate-800 text-lg leading-relaxed space-y-4" v-html="article.content">
                    </div>
                </div>

                <!-- Tags Section -->
                <div v-if="article.tags?.length" class="mt-12 pt-8 border-t border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Related Tags</p>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="tag in article.tags" :key="tag.id" 
                            class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-600">
                            #{{ tag.name }}
                        </span>
                    </div>
                </div>

                <!-- 4. SEO & Metrics (Stacked) -->
                <div class="space-y-8 pt-12 border-t border-slate-100">
                    <!-- Google Preview -->
                    <div class="bg-[#f8f9fa] rounded-3xl p-8 border border-slate-200 shadow-sm">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">search</span>
                            Google Search Preview
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full bg-white border border-slate-200 flex items-center justify-center">
                                    <span class="text-[10px] font-bold text-slate-400">G</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-slate-600 leading-none">Your Website</span>
                                    <span class="text-[9px] text-slate-400 leading-none mt-0.5">https://example.com/{{ article.slug }}</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xl text-[#1a0dab] hover:underline cursor-pointer font-medium leading-tight mb-1">
                                    {{ article.seo_title || article.title }}
                                </h5>
                                <p class="text-sm text-[#4d5156] line-clamp-3 leading-relaxed">
                                    {{ article.seo_description || article.excerpt || 'No description provided.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Analytics -->
                    <div class="p-8 rounded-3xl bg-[#f8f9fa] border border-slate-200 space-y-8 shadow-sm">
                         <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-primary">analytics</span>
                            SEO Analytics
                        </h4>
                        <div class="space-y-6">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-black mb-1">Focus Keyword</p>
                                <p class="text-sm font-bold bg-white px-4 py-2 rounded-xl border border-slate-100 text-slate-700 shadow-sm w-fit">
                                    {{ article.seo_focus_keyword || 'Not Set' }}
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
                                    <p class="text-[9px] text-slate-400 uppercase font-black mb-1">Reading Time</p>
                                    <p class="text-base font-bold text-slate-700">~{{ Math.ceil((article.content?.split(' ').length || 0) / 200) }} min</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
                                    <p class="text-[9px] text-slate-400 uppercase font-black mb-1">Word Count</p>
                                    <p class="text-base font-bold text-slate-700">{{ article.content?.split(' ').length || 0 }} words</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between w-full">
                <BaseButton variant="ghost" @click="emit('close')" icon="close">Close</BaseButton>
                <div class="flex items-center gap-3">
                    <BaseButton @click="handleEdit" icon="edit" class="px-8 shadow-lg shadow-primary/20">Edit Article</BaseButton>
                </div>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
.article-body-content :deep(p) { margin-bottom: 1.5rem; }
.article-body-content :deep(h2) { font-size: 1.5rem; font-weight: 800; margin-top: 2.5rem; margin-bottom: 1rem; color: #0f172a; }
.article-body-content :deep(h3) { font-size: 1.25rem; font-weight: 700; margin-top: 2rem; margin-bottom: 0.75rem; color: #1e293b; }
.article-body-content :deep(img) { border-radius: 1rem; margin: 2rem 0; }
</style>
