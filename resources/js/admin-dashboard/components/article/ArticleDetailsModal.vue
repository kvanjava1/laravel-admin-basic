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

const readingTime = () => {
    const words = props.article?.content?.split(' ').length || 0;
    return Math.max(1, Math.ceil(words / 200));
};

const wordCount = () => {
    return props.article?.content?.split(' ').length || 0;
};

const handleEdit = () => {
    if (!props.article?.id) return;
    emit('close');
    router.push({ name: 'articles.edit', params: { id: props.article.id } });
};
</script>

<template>
    <BaseModal :show="show" title="Article Preview" icon="visibility" @close="emit('close')">
        <div v-if="article" class="divide-y divide-slate-100">

            <!-- Header -->
            <div class="space-y-5 px-8 pt-8 pb-6">
                <div class="flex flex-wrap items-center gap-4">
                    <ArticleStatusBadge :status="article.status" />
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        <span class="text-[11px] font-black uppercase tracking-widest">{{ formatDate(article.published_at) }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-3xl font-black text-slate-900 leading-tight tracking-tight">
                        {{ article.title }}
                    </h1>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-slate-300">link</span>
                        <span class="text-xs font-medium text-slate-400">/{{ article.slug }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 w-fit">
                    <div class="h-11 w-11 rounded-full bg-slate-900 border-4 border-white shadow-sm flex items-center justify-center text-white font-black text-base overflow-hidden shrink-0">
                        <img v-if="article.author?.avatar" :src="'/storage/' + article.author.avatar" class="w-full h-full object-cover">
                        <template v-else>{{ article.author?.name?.charAt(0) || 'A' }}</template>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">Written By</p>
                        <p class="text-sm font-bold text-slate-900 leading-none">{{ article.author?.name || 'Administrator' }}</p>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            <div v-if="article.featured_image" class="px-8 py-6">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                    <div class="aspect-video overflow-hidden bg-slate-100">
                        <img :src="article.featured_image.original_url" class="h-full w-full object-cover">
                    </div>
                    <div v-if="article.featured_image_caption" class="border-t border-slate-200 px-5 py-3">
                        <p class="text-xs font-medium text-slate-500 italic">{{ article.featured_image_caption }}</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="space-y-6 px-8 py-6">
                <div v-if="article.excerpt" class="text-base text-slate-600 font-medium leading-relaxed border-l-4 border-primary pl-6 italic"
                    v-html="article.excerpt">
                </div>
                <div class="article-body-content text-slate-800 leading-relaxed space-y-4" v-html="article.content">
                </div>
            </div>

            <!-- Metadata Grid -->
            <div class="px-8 py-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100 col-span-2 md:col-span-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Category</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ article.category?.name || 'Uncategorized' }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100 col-span-2 md:col-span-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Tags</p>
                        <div v-if="article.tags?.length" class="mt-2 flex flex-wrap gap-1.5">
                            <span v-for="tag in article.tags" :key="tag.id"
                                class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                                {{ tag.name }}
                            </span>
                        </div>
                        <p v-else class="mt-1 text-sm font-bold text-slate-400">-</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Reading Time</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">~{{ readingTime() }} Min</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Word Count</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ wordCount() }} Words</p>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="px-8 py-6 space-y-6">
                <div>
                    <h4 class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">SEO</h4>
                    <p class="mt-1 text-sm font-medium text-slate-500">Google search preview & keyword.</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100 space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded-full bg-white border border-slate-200 flex items-center justify-center shrink-0">
                            <span class="text-[10px] font-bold text-slate-400">G</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-slate-600 leading-none">Your Website</span>
                            <span class="text-[9px] text-slate-400 leading-none mt-0.5">https://example.com/{{ article.slug }}</span>
                        </div>
                    </div>
                    <div>
                        <h5 class="text-lg text-[#1a0dab] hover:underline cursor-pointer font-medium leading-tight mb-1">
                            {{ article.seo_title || article.title }}
                        </h5>
                        <p class="text-sm text-[#4d5156] line-clamp-2 leading-relaxed">
                            {{ article.seo_description || article.excerpt || 'No description provided.' }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-white px-4 py-3 border border-slate-200">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Focus Keyword</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ article.seo_focus_keyword || 'Not Set' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between w-full">
                <BaseButton variant="ghost" @click="emit('close')" icon="close">Close</BaseButton>
                <BaseButton @click="handleEdit" icon="edit" class="px-8 shadow-lg shadow-primary/20">Edit Article</BaseButton>
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
