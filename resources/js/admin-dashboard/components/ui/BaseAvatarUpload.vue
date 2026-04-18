<script setup lang="ts">
/**
 * BaseAvatarUpload.vue
 * An atomic component for displaying and selecting a profile picture/avatar.
 * Emits the raw image data URL so the parent can handle cropping or uploading.
 */
import { ref } from 'vue';

defineProps<{
    modelValue?: string;
}>();

const emit = defineEmits<{
    (e: 'select', data: { dataUrl: string; file: File }): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        if (e.target?.result) {
            emit('select', {
                dataUrl: e.target.result as string,
                file: file
            });
        }
    };
    reader.readAsDataURL(file);
    target.value = ''; // Reset input so the same file can be selected again if needed
};
</script>

<template>
    <div class="relative group">
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileUpload"
        >
        <div
            @click="fileInput?.click()"
            class="h-32 w-32 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center cursor-pointer overflow-hidden hover:border-primary hover:bg-primary/5 transition-all group-hover:scale-105"
        >
            <img v-if="modelValue" :src="modelValue" class="h-full w-full object-cover">
            <template v-else>
                <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors text-3xl">add_a_photo</span>
                <span class="text-[10px] text-slate-400 font-bold uppercase mt-1">Photo</span>
            </template>
        </div>

        <!-- Edit Badge -->
        <div
            v-if="modelValue"
            @click="fileInput?.click()"
            class="absolute -right-1 -bottom-1 h-8 w-8 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center cursor-pointer text-slate-500 hover:text-primary transition-all"
        >
            <span class="material-symbols-outlined text-[18px]">edit</span>
        </div>
    </div>
</template>
