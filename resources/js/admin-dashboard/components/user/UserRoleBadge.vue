<script setup lang="ts">
/**
 * UserRoleBadge.vue (Generalized for Roles and Permissions)
 * A reusable capsule badge for displaying roles or permissions with an icon.
 */
interface Props {
    label: string;
    icon?: string;
}

withDefaults(defineProps<Props>(), {
    icon: 'shield'
});
</script>

<template>
    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200/60 text-sm font-bold text-slate-500 whitespace-nowrap shadow-sm transition-all hover:bg-slate-100/50 cursor-default">
        <span class="material-symbols-outlined text-[18px] opacity-70">{{ icon }}</span>
        {{ label }}
    </div>
</template>
