<script setup lang="ts">
/**
 * BaseModal.vue
 * A reusable, pre-styled modal component with backdrop and animations.
 */
interface Props {
  show: boolean;
  title?: string;
  icon?: string;
  size?: 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl' | '5xl' | '6xl';
}

const props = withDefaults(defineProps<Props>(), {
  size: '4xl'
});
const emit = defineEmits(['close']);

const sizeClasses = {
  'md': 'max-w-md',
  'lg': 'max-w-lg',
  'xl': 'max-w-xl',
  '2xl': 'max-w-2xl',
  '3xl': 'max-w-3xl',
  '4xl': 'max-w-4xl',
  '5xl': 'max-w-5xl',
  '6xl': 'max-w-6xl',
};
</script>

<template>
  <Teleport to="body">
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div 
          class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" 
          @click="emit('close')"
        ></div>

        <!-- Modal Content -->
        <transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="transform scale-95 opacity-0 translate-y-4"
          enter-to-class="transform scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="transform scale-100 opacity-100 translate-y-0"
          leave-to-class="transform scale-95 opacity-0 translate-y-4"
        >
          <div 
            v-if="show"
            class="relative w-full bg-white rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
            :class="sizeClasses[props.size]"
          >
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
              <div class="flex items-center gap-3">
                <span v-if="icon" class="material-symbols-outlined text-slate-400 text-[24px]">
                  {{ icon }}
                </span>
                <h3 class="text-xl font-bold text-text-primary">{{ title }}</h3>
              </div>
              <button 
                @click="emit('close')"
                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all"
              >
                <span class="material-symbols-outlined text-[20px]">close</span>
              </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto custom-scrollbar">
              <slot></slot>
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-end gap-3">
              <slot name="footer"></slot>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94A3B8;
}
</style>
