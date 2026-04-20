<script setup lang="ts">
/**
 * BaseLabel.vue
 * Standardized label component for all form inputs.
 */
defineProps<{
    value: string;
}>();
</script>

<template>
    <label class="text-sm font-bold text-text-secondary uppercase tracking-wider block mb-1">
        {{ value }}
    </label>
</template>
