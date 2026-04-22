<script setup lang="ts">
/**
 * BaseButton.vue
 * A highly reusable, atomic button component.
 */
import { computed } from 'vue';

interface Props {
  variant?: 'primary' | 'white' | 'ghost' | 'danger' | 'warning';
  size?: 'sm' | 'md' | 'lg';
  icon?: string;
  loading?: boolean;
  disabled?: boolean;
  type?: 'button' | 'submit' | 'reset';
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  loading: false,
  disabled: false
});

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'white':
      return 'bg-white text-primary hover:bg-white/90 shadow-md';
    case 'ghost':
      return 'bg-white text-text-secondary border border-border-light hover:border-primary hover:text-primary transition-all shadow-sm';
    case 'danger':
      return 'bg-rose-500 text-white hover:bg-rose-600 shadow-sm shadow-rose-200';
    case 'warning':
      return 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm shadow-amber-200';
    case 'primary':
    default:
      return 'bg-primary text-white hover:opacity-90 shadow-md shadow-primary/20';
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-3 py-1.5 text-xs rounded-xl';
    case 'lg':
      return 'px-8 py-3 text-base rounded-2xl';
    case 'md':
    default:
      return 'px-4 py-2 text-sm rounded-2xl';
  }
});

const iconSize = computed(() => {
  switch (props.size) {
    case 'sm': return 'text-[18px]';
    case 'lg': return 'text-[24px]';
    case 'md':
    default: return 'text-[20px]';
  }
});
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center gap-2 font-bold transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none',
      variantClasses,
      sizeClasses
    ]"
  >
    <!-- Loading Spinner -->
    <span v-if="loading" class="animate-spin h-4 w-4 border-2 border-current border-t-transparent rounded-full mr-1"></span>
    
    <!-- Icon -->
    <span v-if="icon && !loading" class="material-symbols-outlined shrink-0" :class="iconSize">
      {{ icon }}
    </span>

    <!-- Content -->
    <slot />
  </button>
</template>
