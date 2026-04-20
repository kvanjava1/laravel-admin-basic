<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import BaseLabel from './BaseLabel.vue';

/**
 * BaseTagsInput.vue
 * Advanced chip-based tags input with Autocomplete & Premium UI.
 */
const props = defineProps<{
    modelValue: string[];
    label: string;
    suggestions?: string[];
    placeholder?: string;
    error?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

const inputValue = ref('');
const inputRef = ref<HTMLInputElement | null>(null);
const showDropdown = ref(false);
const selectedIndex = ref(-1);

const filteredSuggestions = computed(() => {
    if (!inputValue.value) return [];
    const query = inputValue.value.toLowerCase();
    return (props.suggestions || [])
        .filter(s => s.toLowerCase().includes(query) && !props.modelValue.includes(s))
        .slice(0, 10);
});

const addTag = (tag?: string) => {
    const finalTag = (tag || inputValue.value).trim().replace(/,$/, '');
    if (finalTag && !props.modelValue.includes(finalTag)) {
        emit('update:modelValue', [...props.modelValue, finalTag]);
    }
    inputValue.value = '';
    showDropdown.value = false;
    selectedIndex.value = -1;
};

const removeTag = (index: number) => {
    const newTags = [...props.modelValue];
    newTags.splice(index, 1);
    emit('update:modelValue', newTags);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        if (selectedIndex.value >= 0 && filteredSuggestions.value[selectedIndex.value]) {
            addTag(filteredSuggestions.value[selectedIndex.value]);
        } else {
            addTag();
        }
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        showDropdown.value = true;
        if (selectedIndex.value < filteredSuggestions.value.length - 1) {
            selectedIndex.value++;
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (selectedIndex.value > 0) {
            selectedIndex.value--;
        }
    } else if (e.key === 'Escape') {
        showDropdown.value = false;
    } else if (e.key === 'Backspace' && !inputValue.value && props.modelValue.length > 0) {
        removeTag(props.modelValue.length - 1);
    }
};

const selectSuggestion = (suggestion: string) => {
    addTag(suggestion);
    inputRef.value?.focus();
};

const onClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.tags-input-container')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', onClickOutside);
});

const focusInput = () => {
    inputRef.value?.focus();
    if (filteredSuggestions.value.length > 0) showDropdown.value = true;
};
</script>

<template>
    <div class="flex flex-col gap-1 relative tags-input-container">
        <BaseLabel :value="label" />
        
        <div 
            @click="focusInput"
            :class="[
                error ? 'border-rose-400 bg-rose-50/30 ring-2 ring-rose-50' : 'bg-slate-50 border-slate-200 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary',
            ]"
            class="min-h-[52px] w-full px-3 py-2 border rounded-2xl flex flex-wrap gap-2 transition-all cursor-text shadow-sm relative z-20"
        >
            <!-- Tag Chips -->
            <TransitionGroup name="tag-list">
                <div 
                    v-for="(tag, index) in modelValue" 
                    :key="tag"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold shadow-sm hover:border-primary transition-colors group"
                >
                    <span>{{ tag }}</span>
                    <button 
                        @click.stop="removeTag(index)" 
                        type="button"
                        class="material-symbols-outlined text-[16px] text-slate-400 hover:text-rose-500 transition-colors"
                    >
                        cancel
                    </button>
                </div>
            </TransitionGroup>

            <!-- Invisible Input -->
            <input
                ref="inputRef"
                v-model="inputValue"
                type="text"
                :placeholder="modelValue.length === 0 ? (placeholder || 'Add tags...') : ''"
                class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-slate-700 text-base placeholder:text-slate-400 placeholder:font-medium py-1"
                @keydown="handleKeydown"
                @input="showDropdown = true"
                @focus="showDropdown = filteredSuggestions.length > 0"
            />

            <!-- Autocomplete Dropdown (Moved inside relative wrapper for correct positioning) -->
            <Transition name="fade-down">
                <div 
                    v-if="showDropdown && filteredSuggestions.length > 0"
                    class="absolute left-0 right-0 top-[calc(100%+4px)] bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-50 max-h-60 overflow-y-auto"
                >
                    <div class="p-2">
                        <button
                            v-for="(suggestion, i) in filteredSuggestions"
                            :key="suggestion"
                            @click="selectSuggestion(suggestion)"
                            class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-between group"
                            :class="selectedIndex === i ? 'bg-primary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50'"
                        >
                            <span>{{ suggestion }}</span>
                            <span 
                                class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity"
                                :class="selectedIndex === i ? 'text-white' : 'text-primary'"
                            >
                                add_circle
                            </span>
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
        
        <div v-if="error" class="flex items-center gap-1 mt-1 ml-1 text-rose-600 animate-in fade-in slide-in-from-top-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            <p class="text-sm font-bold leading-none tracking-tight">
                {{ error }}
            </p>
        </div>
        <p v-else class="text-[10px] text-slate-400 font-bold uppercase tracking-widest ml-1">
            Press <span class="text-slate-600">Enter</span> to add or use <span class="text-slate-600">Arrows</span> to navigate suggestions
        </p>
    </div>
</template>

<style scoped>
.tag-list-enter-active,
.tag-list-leave-active,
.fade-down-enter-active,
.fade-down-leave-active {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.tag-list-enter-from,
.tag-list-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

.fade-down-enter-from,
.fade-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.tags-input-container :deep(input::placeholder) {
    transition: opacity 0.2s ease;
}

.tags-input-container :deep(input:focus::placeholder) {
    opacity: 0.5;
}
</style>
