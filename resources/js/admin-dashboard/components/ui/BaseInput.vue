<script setup lang="ts">
import BaseLabel from './BaseLabel.vue';

defineProps<{
    label: string;
    modelValue: string;
    type?: string;
    placeholder?: string;
    icon?: string;
    error?: string;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-1">
        <BaseLabel :value="label" />
        <div class="relative">
            <span v-if="icon"
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]"
                :class="{ 'text-rose-400': error }">{{ icon }}</span>
            <input :type="type ?? 'text'" :value="modelValue" :placeholder="placeholder" :class="[
                icon ? 'pl-12' : 'px-4',
                error ? 'border-rose-400 bg-rose-50/30 focus:ring-rose-200 focus:border-rose-500' : 'border-slate-200 bg-slate-50 focus:ring-primary/20 focus:border-primary'
            ]" class="w-full border rounded-xl py-3 pr-4 text-base focus:outline-none focus:ring-2 transition-all"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)">
        </div>
        <div v-if="error"
            class="flex items-center gap-1 mt-1 ml-1 text-rose-600 animate-in fade-in slide-in-from-top-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            <p class="text-sm font-bold leading-none tracking-tight">
                {{ error }}
            </p>
        </div>
    </div>
</template>
