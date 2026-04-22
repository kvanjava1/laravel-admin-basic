<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { mediaService } from '../../services/mediaService';
import BaseModal from '../ui/BaseModal.vue';
import BaseButton from '../ui/BaseButton.vue';
import BaseInput from '../ui/BaseInput.vue';
import MediaUploadWorkflow from '../media/MediaUploadWorkflow.vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close', 'select']);

const activeTab = ref<'browse' | 'upload'>('browse');
const searchQuery = ref('');
const mediaItems = ref<any[]>([]);
const isLoading = ref(false);
const selectedItem = ref<any | null>(null);

const fetchMedia = async () => {
    isLoading.value = true;
    try {
        const response = await mediaService.getPaginated({
            title: searchQuery.value,
            limit: 20
        });
        if (response.success) {
            mediaItems.value = response.data.data || [];
        }
    } catch (error) {
        console.error('Failed to fetch media', error);
    } finally {
        isLoading.value = false;
    }
};

const selectItem = (item: any) => {
    selectedItem.value = item;
};

const confirmSelection = (ratioType: '16_9' | '4_3' | 'original') => {
    if (!selectedItem.value) return;

    let url = selectedItem.value.original_url;
    if (ratioType === '16_9') url = selectedItem.value.ratio_16_9_big_url || selectedItem.value.ratio_16_9_url || url;
    if (ratioType === '4_3') url = selectedItem.value.ratio_4_3_big_url || selectedItem.value.ratio_4_3_url || url;

    emit('select', url, selectedItem.value);
    emit('close');
    selectedItem.value = null;
};

const handleUploadSuccess = (item: any) => {
    selectItem(item);
};

// Fetch media when modal is shown
watch(() => props.show, (isShown) => {
    if (isShown) {
        fetchMedia();
    }
});

onMounted(() => {
    if (props.show) fetchMedia();
});
</script>

<template>
    <BaseModal :show="show" @close="emit('close')" title="Media Library">
        <div class="flex flex-col h-[700px]">
            <!-- Tabs -->
            <div class="flex border-b border-slate-200 px-6">
                <button @click="activeTab = 'browse'"
                    :class="{ 'border-primary text-primary': activeTab === 'browse', 'border-transparent text-slate-500 hover:text-slate-700': activeTab !== 'browse' }"
                    class="px-4 py-3 border-b-2 text-sm font-bold uppercase tracking-widest transition-all">Browse
                    Library</button>
                <button @click="activeTab = 'upload'"
                    :class="{ 'border-primary text-primary': activeTab === 'upload', 'border-transparent text-slate-500 hover:text-slate-700': activeTab !== 'upload' }"
                    class="px-4 py-3 border-b-2 text-sm font-bold uppercase tracking-widest transition-all">Upload
                    New</button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
                <!-- Browse Tab -->
                <div v-if="activeTab === 'browse'" class="space-y-6">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <BaseInput label="" v-model="searchQuery" placeholder="Search images by title..."
                                icon="search" @keyup.enter="fetchMedia" />
                        </div>
                        <BaseButton icon="search" size="lg" @click="fetchMedia" :loading="isLoading" class="mb-0.5">
                            Search</BaseButton>
                    </div>

                    <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div v-for="i in 10" :key="i" class="aspect-[4/3] bg-slate-200 animate-pulse rounded-2xl"></div>
                    </div>

                    <div v-else-if="mediaItems.length > 0"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div v-for="item in mediaItems" :key="item.id" @click="selectItem(item)"
                            class="group relative aspect-[4/3] bg-white rounded-2xl border border-slate-200 overflow-hidden cursor-pointer hover:border-primary hover:shadow-lg transition-all">
                            <img :src="item.thumbnail_url" class="w-full h-full object-cover">
                            <div
                                class="absolute inset-0 bg-primary/80 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <span class="material-symbols-outlined text-white text-3xl">add_circle</span>
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent">
                                <p class="text-[9px] text-white font-bold truncate">{{ item.title }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-20 text-center">
                        <span class="material-symbols-outlined text-slate-300 text-6xl">image_not_supported</span>
                        <p class="mt-4 text-slate-500 font-medium">No images found in library.</p>
                        <BaseButton variant="ghost" class="mt-4" @click="activeTab = 'upload'">Upload your first image
                        </BaseButton>
                    </div>
                </div>

                <!-- Upload Tab -->
                <div v-else class="pb-10">
                    <MediaUploadWorkflow is-modal @success="handleUploadSuccess" />
                </div>
            </div>

            <!-- Ratio Selection Overlay -->
            <div v-if="selectedItem" class="absolute inset-0 bg-white z-20 flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-3">
                        <button @click="selectedItem = null"
                            class="material-symbols-outlined text-slate-400 hover:text-primary transition-colors">arrow_back</button>
                        <h4 class="text-lg font-bold text-slate-800">Choose Image Ratio</h4>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Standard
                        Compliance</span>
                </div>

                <div class="flex-1 overflow-y-auto p-8 flex flex-col items-center justify-center space-y-12">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-4xl">
                        <!-- 16:9 Option -->
                        <div @click="confirmSelection('16_9')" class="group cursor-pointer space-y-4 text-center">
                            <div
                                class="aspect-video rounded-2xl overflow-hidden border-2 border-slate-200 group-hover:border-primary group-hover:shadow-xl transition-all relative">
                                <img :src="selectedItem.ratio_16_9_big_url || selectedItem.ratio_16_9_url || selectedItem.original_url"
                                    class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span
                                        class="bg-primary text-white px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest">Select
                                        16:9 (High Res)</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800">Headline Ratio</h5>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">16:9 Wide</p>
                            </div>
                        </div>

                        <!-- 4:3 Option -->
                        <div @click="confirmSelection('4_3')" class="group cursor-pointer space-y-4 text-center">
                            <div
                                class="aspect-[4/3] rounded-2xl overflow-hidden border-2 border-slate-200 group-hover:border-primary group-hover:shadow-xl transition-all relative">
                                <img :src="selectedItem.ratio_4_3_big_url || selectedItem.ratio_4_3_url || selectedItem.original_url"
                                    class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span
                                        class="bg-primary text-white px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest">Select
                                        4:3 (High Res)</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800">Thumbnail Ratio</h5>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">4:3 Standard
                                </p>
                            </div>
                        </div>

                        <!-- Original Option -->
                        <div @click="confirmSelection('original')" class="group cursor-pointer space-y-4 text-center">
                            <div
                                class="aspect-square rounded-2xl overflow-hidden border-2 border-slate-200 group-hover:border-primary group-hover:shadow-xl transition-all relative">
                                <img :src="selectedItem.original_url" class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span
                                        class="bg-primary text-white px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest">Select
                                        Original</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800">Original</h5>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Native Aspect
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-md text-center">
                        <p class="text-sm text-slate-500 italic">"Select the appropriate aspect ratio for your website layout to ensure a premium frontend design."</p>
                    </div>
                </div>
            </div>
        </div>
    </BaseModal>
</template>
