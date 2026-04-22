<script setup lang="ts">
import BaseLabel from './BaseLabel.vue';

defineProps<{
    label?: string;
    modelValue: string | number | null;
    type?: string;
    placeholder?: string;
    icon?: string;
    error?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-1.5 w-full">
        <BaseLabel v-if="label" :value="label" :required="required" :class="{ 'text-rose-500': error }" />
        
        <div class="relative group">
            <!-- Icon -->
            <span v-if="icon"
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] transition-colors duration-300"
                :class="[
                    error ? 'text-rose-500' : 'text-slate-400 group-focus-within:text-primary'
                ]">
                {{ icon }}
            </span>

            <!-- Input -->
            <textarea
                v-if="type === 'textarea'"
                :value="(modelValue as string)" 
                :placeholder="placeholder" 
                :disabled="disabled"
                :readonly="readonly"
                rows="3"
                :class="[
                    icon ? 'pl-12' : 'px-4',
                    error 
                        ? 'border-rose-300 bg-rose-50/30 focus:border-rose-500 focus:ring-rose-200/50 animate-shake' 
                        : 'border-slate-200 bg-slate-50 focus:border-primary focus:ring-primary/10 hover:border-slate-300',
                    disabled ? 'opacity-60 cursor-not-allowed bg-slate-100' : ''
                ]" 
                class="w-full border rounded-xl py-3 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:ring-4 transition-all duration-300 placeholder:text-slate-400 resize-none"
                @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>

            <input 
                v-else
                :type="type ?? 'text'" 
                :value="modelValue" 
                :placeholder="placeholder" 
                :disabled="disabled"
                :readonly="readonly"
                :class="[
                    icon ? 'pl-12' : 'px-4',
                    error 
                        ? 'border-rose-300 bg-rose-50/30 focus:border-rose-500 focus:ring-rose-200/50 animate-shake' 
                        : 'border-slate-200 bg-slate-50 focus:border-primary focus:ring-primary/10 hover:border-slate-300',
                    disabled ? 'opacity-60 cursor-not-allowed bg-slate-100' : ''
                ]" 
                class="w-full border rounded-xl py-3 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:ring-4 transition-all duration-300 placeholder:text-slate-400"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            >

            <!-- Error Indicator Icon (Right side) -->
            <div v-if="error" class="absolute right-4 top-[14px] text-rose-500 flex items-center">
                <span class="material-symbols-outlined text-[20px] animate-pulse">error</span>
            </div>
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
