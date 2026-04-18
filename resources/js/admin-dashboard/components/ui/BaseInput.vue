<script setup lang="ts">
/**
 * BaseInput.vue
 * Reusable atomic text/email input with label and optional leading icon.
 */
defineProps<{
    label: string;
    modelValue: string;
    type?: string;
    placeholder?: string;
    icon?: string;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-2">
        <label class="text-sm font-bold text-text-secondary uppercase tracking-wider">{{ label }}</label>
        <div class="relative">
            <span
                v-if="icon"
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]"
            >{{ icon }}</span>
            <input
                :type="type ?? 'text'"
                :value="modelValue"
                :placeholder="placeholder"
                :class="icon ? 'pl-12' : 'px-4'"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            >
        </div>
    </div>
</template>
