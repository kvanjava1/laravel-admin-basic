<script setup lang="ts">
import { ref } from 'vue';
import BaseModal from '../ui/BaseModal.vue';
import ArticleStatusBadge from './ArticleStatusBadge.vue';

interface Props {
    show: boolean;
    article: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <BaseModal :show="show" title="Article Details" icon="description" @close="emit('close')" size="3xl">
        <div v-if="article" class="space-y-8">
            <!-- Header Image -->
            <div v-if="article.featured_image"
                class="w-full aspect-video rounded-2xl overflow-hidden border-4 border-white shadow-xl bg-slate-100">
                <img :src="article.featured_image.original_url" class="w-full h-full object-cover">
            </div>

            <!-- Title & Metadata -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <ArticleStatusBadge :status="article.status" />
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400">{{ article.category?.name
                        || 'Uncategorized' }}</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 leading-tight">{{ article.title }}</h2>

                <div class="flex items-center gap-6 py-4 border-y border-slate-100">
                    <div class="flex items-center gap-2">
                        <div
                            class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                            {{ article.author?.name?.charAt(0) || 'A' }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Author</span>
                            <span class="text-sm font-bold text-slate-700 leading-none">{{ article.author?.name ||
                                'Unknown' }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Published
                            At</span>
                        <span class="text-sm font-bold text-slate-700 leading-none">{{ formatDate(article.published_at)
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div
                class="prose prose-slate max-w-none prose-p:leading-relaxed prose-headings:text-slate-900 prose-img:rounded-2xl">
                <div class="text-xl text-slate-600 italic font-serif border-l-4 border-primary/20 pl-6 py-2 mb-8"
                    v-html="article.excerpt"></div>
                <div class="text-slate-800" v-html="article.content"></div>
            </div>

            <!-- SEO Preview -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">search</span>
                    SEO Metadata
                </h4>
                <div class="space-y-3">
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <p class="text-[11px] text-[#1a0dab]">google.com/search</p>
                        <h5 class="text-xl text-[#1a0dab] hover:underline cursor-pointer">{{ article.seo_title }}</h5>
                        <p class="text-sm text-[#4d5156] line-clamp-2 mt-1">{{ article.seo_description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <BaseButton variant="ghost" @click="emit('close')">Close Detail</BaseButton>
        </template>
    </BaseModal>
</template>
