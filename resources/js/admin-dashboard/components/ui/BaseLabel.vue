<script setup lang="ts">
/**
 * BaseLabel.vue
 * Standardized label component for all form inputs.
 */
defineProps<{
    value: string;
    required?: boolean;
}>();
</script>

<template>
    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider block mb-1">
        {{ value }}
        <span v-if="required" class="text-rose-500 ml-0.5">*</span>
    </label>
</template>
