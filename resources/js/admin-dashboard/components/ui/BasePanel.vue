<script setup lang="ts">
/**
 * BasePanel.vue
 * A simple wrapper component that provides the premium "Panel" look for tables or forms.
 */
interface Props {
    title: string;
    icon?: string;
    stackedHeader?: boolean;
}

withDefaults(defineProps<Props>(), {
    stackedHeader: false
});
</script>

<template>
    <div class="light-panel rounded-xl p-6 border border-slate-200 shadow-sm flex flex-col">
        <!-- Header with Title and Slots for Buttons -->
        <div 
            class="flex flex-wrap items-start gap-4 mb-4"
            :class="[stackedHeader ? 'flex-col' : 'justify-between items-center']"
        >
            <div class="flex items-center gap-2">
                <span v-if="icon" class="material-symbols-outlined text-primary/60 text-[22px]">{{ icon }}</span>
                <h3 class="text-lg font-bold text-text-primary">{{ title }}</h3>
            </div>

            <div class="flex items-center gap-2" :class="{'w-full': stackedHeader}">
                <slot name="actions"></slot>
            </div>
        </div>

        <!-- Slot for non-scrollable content (e.g., Notifications) -->
        <div v-if="$slots['top-content']" class="mb-4">
            <slot name="top-content"></slot>
        </div>

        <!-- This is where your actual <table> goes -->
        <div class="w-full overflow-x-auto">
            <slot></slot>
        </div>

        <!-- Optional slot for pagination or form footer -->
        <div v-if="$slots.footer" class="mt-8 pt-4 border-t border-slate-200">
            <slot name="footer"></slot>
        </div>
    </div>
</template>
