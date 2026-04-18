<script setup lang="ts">
/**
 * BaseSelect.vue
 * Reusable atomic select/dropdown with label and chevron icon.
 */
defineProps<{
    label: string;
    modelValue: string | number;
    options: (string | { label: string; value: string | number; disabled?: boolean })[];
    placeholder?: string;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-2">
        <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">{{ label }}</label>
        <div class="relative">
            <select
                :value="modelValue"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 pr-10 text-base appearance-none bg-none focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                :class="modelValue === '' ? 'text-slate-400' : 'text-text-primary'"
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
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">expand_more</span>
        </div>
    </div>
</template>

