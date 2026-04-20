<script setup lang="ts">
/**
 * BasePageHeader.vue
 * A reusable component for page titles, subtitles, and primary actions.
 * Supports "primary" (Index pages) and "secondary" (Create/Edit pages) layouts.
 */
import BaseButton from './BaseButton.vue';

interface Props {
    title: string;
    subtitle?: string;
    // For "Secondary" mode (Create/Edit)
    backLabel?: string;
    backIcon?: string;
    backRouteName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    backLabel: 'Back',
    backIcon: 'arrow_back'
});

const emit = defineEmits(['back']);
</script>

<template>
    <div class="mb-4">
        <!-- Layout for Creation/Edit Pages (Secondary) -->
        <div v-if="backRouteName || $attrs.onBack" class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-text-primary">{{ title }}</h2>
                <p v-if="subtitle" class="text-text-secondary text-sm font-medium mt-1">{{ subtitle }}</p>
            </div>
            <div class="flex items-center gap-2">
                <slot name="actions" />
                <BaseButton 
                    variant="ghost" 
                    :icon="backIcon" 
                    @click="backRouteName ? $router.push({ name: backRouteName }) : emit('back')"
                >
                    {{ backLabel }}
                </BaseButton>
            </div>
        </div>

        <!-- Layout for Index/Dashboard (Primary) -->
        <div v-else class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-text-primary tracking-tight">{{ title }}</h1>
                <p v-if="subtitle" class="text-text-secondary mt-1 text-sm font-medium">{{ subtitle }}</p>
            </div>
            <!-- Slot for primary actions like "Add User" -->
            <div class="flex items-center gap-3">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
