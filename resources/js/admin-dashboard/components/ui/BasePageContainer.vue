<script setup lang="ts">
/**
 * BasePageContainer.vue
 * A reusable wrapper for pages to standardize width and vertical spacing.
 */
interface Props {
    variant?: 'narrow' | 'wide' | 'full';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'narrow'
});
</script>

<template>
    <div 
        class="space-y-6 mx-auto"
        :class="{
            'max-w-4xl': variant === 'narrow',
            'max-w-5xl': variant === 'wide',
            'max-w-none': variant === 'full'
        }"
    >
        <slot />
    </div>
</template>
