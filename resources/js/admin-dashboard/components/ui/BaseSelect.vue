<script setup lang="ts">
import BaseLabel from './BaseLabel.vue';

/**
 * BaseSelect.vue
 * Reusable atomic select/dropdown with label and chevron icon.
 */
defineProps<{
    label: string;
    modelValue: string | number | null;
    options: (string | { label: string; value: string | number; disabled?: boolean })[];
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    required?: boolean;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-1.5 w-full">
        <BaseLabel :value="label" :required="required" :class="{ 'text-rose-500': error }" />
        
        <div class="relative group">
            <select
                :value="modelValue"
                :disabled="disabled"
                :class="[
                    (!modelValue || modelValue === '') ? 'text-slate-400' : 'text-slate-700',
                    error 
                        ? 'border-rose-300 bg-rose-50/30 focus:border-rose-500 focus:ring-rose-200/50 animate-shake' 
                        : 'border-slate-200 bg-slate-50 focus:border-primary focus:ring-primary/10 hover:border-slate-300',
                    disabled ? 'opacity-60 cursor-not-allowed bg-slate-100' : ''
                ]"
                class="w-full border rounded-xl py-3 px-4 pr-10 text-sm font-medium appearance-none bg-none focus:outline-none focus:ring-4 transition-all duration-300"
                @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
            >
                <option value="" disabled hidden>{{ placeholder || `Select ${label}` }}</option>
                <option 
                    v-for="(opt, i) in options" 
                    :key="i" 
                    :value="typeof opt === 'object' ? opt.value : opt" 
                    :disabled="typeof opt === 'object' ? opt.disabled : false"
                    class="text-slate-700 bg-white"
                >
                    {{ typeof opt === 'object' ? opt.label : opt }}
                </option>
            </select>
            
            <!-- Chevron Icon -->
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-[20px] pointer-events-none transition-colors"
                :class="[
                    error ? 'text-rose-500' : 'text-slate-400 group-focus-within:text-primary'
                ]">
                expand_more
            </span>
        </div>

        <!-- Error Message -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-2 opacity-0"
        >
            <div v-if="error" class="flex items-center gap-1.5 mt-2 ml-1">
                <span class="material-symbols-outlined text-[18px] font-black text-rose-600">error</span>
                <p class="text-sm font-black text-rose-600 leading-none">
                    {{ error }}
                </p>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    50% { transform: translateX(4px); }
    75% { transform: translateX(-4px); }
}

.animate-shake {
    animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
}
</style>
