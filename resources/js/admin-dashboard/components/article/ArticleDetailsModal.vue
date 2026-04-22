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
    <BaseModal :show="show" title="Article Preview" icon="visibility" @close="emit('close')">
        <div v-if="article" class="space-y-10 pb-6">
            <!-- 1. Featured Image Card (Premium Style) -->
            <div v-if="article.featured_image" class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm">
                <div class="aspect-video overflow-hidden bg-slate-100">
                    <img :src="article.featured_image.original_url" class="h-full w-full object-cover">
                </div>
                <div class="space-y-4 border-t border-slate-200 p-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Title Image</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ article.featured_image_title || article.featured_image?.title || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Category</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ article.category?.name || 'Uncategorized' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Caption</p>
                        <p class="mt-1 text-sm font-bold text-slate-800 italic">{{ article.featured_image_caption || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Tags</p>
                        <div v-if="article.tags?.length" class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="tag in article.tags"
                                :key="tag.id"
                                class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                        <p v-else class="mt-1 text-sm font-bold text-slate-800">-</p>
                    </div>
                </div>
            </div>
            <div v-else class="w-full h-40 bg-slate-50 flex items-center justify-center border border-dashed border-slate-200 rounded-3xl">
                <span class="material-symbols-outlined text-slate-300 text-4xl">image</span>
            </div>

            <!-- 2. Article Content Card (Premium Style) -->
            <section class="rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <div class="p-8 space-y-10">
                    <!-- Header & Metadata -->
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center gap-4">
                            <ArticleStatusBadge :status="article.status" />
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                <span class="text-[11px] font-black uppercase tracking-widest">{{ formatDate(article.published_at) }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h1 class="text-4xl font-black text-slate-900 leading-[1.1] tracking-tight">
                                {{ article.title }}
                            </h1>
                            
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-slate-300">link</span>
                                <span class="text-xs font-medium text-slate-400">/{{ article.slug }}</span>
                            </div>
                        </div>

                        <!-- Author Card (Silver Style) -->
                        <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 w-fit">
                            <div class="h-12 w-12 rounded-full bg-slate-900 border-4 border-white shadow-sm flex items-center justify-center text-white font-black text-lg overflow-hidden">
                                <img v-if="article.author?.avatar" :src="'/storage/' + article.author.avatar" class="w-full h-full object-cover">
                                <template v-else>
                                    {{ article.author?.name?.charAt(0) || 'A' }}
                                </template>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Written By</p>
                                <p class="text-base font-bold text-slate-900 leading-none">{{ article.author?.name || 'Administrator' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100"></div>

                    <!-- Content Body -->
                    <div class="prose prose-slate max-w-none">
                        <!-- Excerpt -->
                        <div v-if="article.excerpt" class="text-xl text-slate-600 font-medium leading-relaxed border-l-4 border-primary pl-8 mb-10 italic"
                            v-html="article.excerpt">
                        </div>
                        
                        <!-- Main Body -->
                        <div class="article-body-content text-slate-800 text-lg leading-relaxed space-y-4" v-html="article.content">
                        </div>
                    </div>
                </div>
            </section>


                <!-- 4. Combined SEO & Metrics Card -->
                <div>
                    <section class="rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                        <!-- Google Preview Section -->
                        <div class="p-6 space-y-6">
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">Google Search Preview</h4>
                                <p class="mt-1 text-sm font-medium text-slate-500">Visualize how this article appears in Google search results.</p>
                            </div>
                            
                            <div class="rounded-2xl bg-slate-50 p-6 border border-slate-100">
                                <div class="space-y-3">
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
                                        <p class="text-sm text-[#4d5156] line-clamp-2 leading-relaxed">
                                            {{ article.seo_description || article.excerpt || 'No description provided.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-slate-100 mx-6"></div>

                        <!-- SEO Analytics Section -->
                        <div class="p-6 space-y-6">
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">SEO Analytics</h4>
                                <p class="mt-1 text-sm font-medium text-slate-500">Performance metrics and content keyword analysis.</p>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100">
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Focus Keyword</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800">{{ article.seo_focus_keyword || 'Not Set' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100">
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Reading Time</p>
                                        <p class="mt-1 text-sm font-bold text-slate-800">~{{ Math.ceil((article.content?.split(' ').length || 0) / 200) }} Min</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100">
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Word Count</p>
                                        <p class="mt-1 text-sm font-bold text-slate-800">{{ article.content?.split(' ').length || 0 }} Words</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
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
