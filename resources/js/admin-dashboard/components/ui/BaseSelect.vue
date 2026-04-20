<script setup lang="ts">
import BaseLabel from './BaseLabel.vue';

/**
 * BaseSelect.vue
 * Reusable atomic select/dropdown with label and chevron icon.
 */
defineProps<{
    label: string;
    modelValue: string | number;
    options: (string | { label: string; value: string | number; disabled?: boolean })[];
    placeholder?: string;
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
            <select
                :value="modelValue"
                :class="[
                    modelValue === '' ? 'text-slate-400' : 'text-text-primary',
                    error ? 'border-rose-400 bg-rose-50/30 focus:ring-rose-200 focus:border-rose-500' : 'border-slate-200 bg-slate-50 focus:ring-primary/20 focus:border-primary'
                ]"
                class="w-full border rounded-xl py-3 px-4 pr-10 text-base appearance-none bg-none focus:outline-none focus:ring-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
            >
                <option value="" disabled hidden>{{ placeholder || `Select ${label}` }}</option>
                <option 
                    v-for="(opt, i) in options" 
                    :key="i" 
                    :value="typeof opt === 'object' ? opt.value : opt" 
                    :disabled="typeof opt === 'object' ? opt.disabled : false"
                    :class="[
                        'text-text-primary',
                        typeof opt === 'object' && opt.disabled ? 'text-slate-400 italic bg-slate-50' : ''
                    ]"
                >
                    {{ typeof opt === 'object' ? opt.label : opt }}
                </option>
            </select>
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none" :class="{ 'text-rose-400': error }">expand_more</span>
        </div>
        <div v-if="error" class="flex items-center gap-1 mt-1 ml-1 text-rose-600 animate-in fade-in slide-in-from-top-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            <p class="text-sm font-bold leading-none tracking-tight">
                {{ error }}
            </p>
        </div>
    </div>
</template>

