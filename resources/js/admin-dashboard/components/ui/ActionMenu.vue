<script lang="ts">
import { ref } from 'vue';
/**
 * SHARED STATE: Ensures only one ActionMenu is open dashboard-wide.
 */
const activeMenuId = ref<number | null>(null);
</script>

<script setup lang="ts">
/**
 * ActionMenu.vue
 * A generic, pre-styled dropdown menu using the Atomic pattern.
 * Supports "Smart Upward Flipping" when used in tables, and uses Teleport to avoid overflow clipping.
 */
import { onBeforeUnmount, computed, getCurrentInstance, watch, nextTick } from 'vue';
import BaseButton from './BaseButton.vue';

interface Action {
    label: string;
    icon: string;
    colorClass?: string;
    divider?: boolean; // Add divider support
    handler: () => void;
}

const props = withDefaults(defineProps<{
    actions: Action[];
    icon?: string;
    variant?: 'primary' | 'white' | 'ghost' | 'danger';
    size?: 'sm' | 'md' | 'lg';
    index?: number;    // Optional: row index for smart flipping
    total?: number;    // Optional: total rows for smart flipping
}>(), {
    icon: 'more_vert',
    variant: 'ghost',
    size: 'sm'
});

// Get unique ID for this instance
const instance = getCurrentInstance();
const menuId = instance?.uid ?? Math.random();

const isOpen = computed(() => activeMenuId.value === menuId);

const containerRef = ref<HTMLElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref({ top: 'auto', bottom: 'auto', left: 'auto', right: 'auto' });

const closeMenu = () => {
    if (activeMenuId.value === menuId) {
        activeMenuId.value = null;
    }
};

// Logic to determine if menu should open UPWARDS
const openUpwards = computed(() => {
    if (props.index !== undefined && props.total !== undefined) {
        return props.index >= props.total - 2 && props.total > 3;
    }
    return false;
});

const isAlignRight = ref(true);

const calculatePosition = () => {
    if (!containerRef.value) return;
    const rect = containerRef.value.getBoundingClientRect();
    const menuWidth = 256; // w-64
    
    // Determine horizontal alignment: if not enough space on the left, align left
    // Otherwise, default to aligning right (standard for action menus)
    isAlignRight.value = rect.left > menuWidth;

    const style: any = {
        top: 'auto',
        bottom: 'auto',
        left: 'auto',
        right: 'auto'
    };

    if (openUpwards.value) {
        style.bottom = `${window.innerHeight - rect.top + 8}px`;
    } else {
        style.top = `${rect.bottom + 8}px`;
    }

    if (isAlignRight.value) {
        style.right = `${window.innerWidth - rect.right}px`;
    } else {
        style.left = `${rect.left}px`;
    }

    dropdownStyle.value = style;
};

const toggleMenu = async () => {
    if (activeMenuId.value === menuId) {
        closeMenu();
    } else {
        activeMenuId.value = menuId;
        await nextTick();
        calculatePosition();
    }
};

const handleClickOutside = (e: MouseEvent) => {
    if (activeMenuId.value !== menuId) return;
    const target = e.target as HTMLElement;
    if (containerRef.value && containerRef.value.contains(target)) return;
    if (menuRef.value && menuRef.value.contains(target)) return;
    closeMenu();
};

const handleScroll = () => {
    if (activeMenuId.value === menuId) {
        calculatePosition();
    }
};

// Listen for clicks outside and scroll to update position when open
watch(isOpen, (newVal) => {
    if (newVal) {
        window.addEventListener('click', handleClickOutside);
        window.addEventListener('scroll', handleScroll, true); // capture phase for any scroll container
        window.addEventListener('resize', handleScroll);
    } else {
        window.removeEventListener('click', handleClickOutside);
        window.removeEventListener('scroll', handleScroll, true);
        window.removeEventListener('resize', handleScroll);
    }
});

onBeforeUnmount(() => {
    if (activeMenuId.value === menuId) {
        closeMenu();
    }
});

// Helper to determine hover class based on colorClass
const getHoverClass = (colorClass?: string) => {
    if (!colorClass) return 'text-text-secondary hover:bg-background-light hover:text-primary';
    
    if (colorClass.includes('text-rose')) return `${colorClass} hover:bg-rose-100`;
    if (colorClass.includes('text-amber')) return `${colorClass} hover:bg-amber-100`;
    if (colorClass.includes('text-emerald')) return `${colorClass} hover:bg-emerald-100`;
    if (colorClass.includes('text-primary')) return `${colorClass} hover:bg-primary/5`;
    
    return `${colorClass} hover:bg-slate-100`;
};
</script>

<template>
    <div class="relative inline-block text-right" ref="containerRef">
        <!-- Trigger Button (uses our Atomic BaseButton) -->
        <BaseButton 
            :variant="variant" 
            :size="size" 
            :icon="icon"
            @click.stop="toggleMenu"
        />

        <!-- Dropdown Menu (Teleported to body to avoid overflow clipping) -->
        <Teleport to="body">
            <div v-if="isOpen"
                ref="menuRef"
                :style="dropdownStyle"
                :class="[
                    (openUpwards ? 'origin-bottom' : 'origin-top') + '-' + (isAlignRight ? 'right' : 'left'),
                    'fixed w-64 bg-white border border-slate-200 rounded-xl shadow-xl z-[100] py-2 ring-1 ring-black/5 transition-all'
                ]">
                <template v-for="(action, i) in actions" :key="i">
                    <!-- Divider -->
                    <div v-if="action.divider" class="my-1.5 border-t border-slate-100 mx-2"></div>
                    
                    <button 
                        @click.stop="action.handler(); closeMenu()"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-2.5 text-base font-semibold transition-colors whitespace-nowrap',
                            getHoverClass(action.colorClass)
                        ]">
                        <span class="material-symbols-outlined text-[20px]">{{ action.icon }}</span>
                        <span>{{ action.label }}</span>
                    </button>
                </template>
            </div>
        </Teleport>
    </div>
</template>
