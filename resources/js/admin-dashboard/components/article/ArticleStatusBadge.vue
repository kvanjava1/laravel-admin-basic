<script setup lang="ts">
/**
 * ArticleStatusBadge.vue
 * Reusable badge for article publication status.
 */

interface Props {
    status: {
        name: string;
        label: string;
        color_class?: string;
    } | string;
}

const props = defineProps<Props>();

// Helper to determine status color if color_class is missing
const getStatusClasses = (status: any) => {
    const name = typeof status === 'string' ? status : (status.name || status.label);
    const statusKey = name?.toLowerCase();

    switch (statusKey) {
        case 'published':
            return 'text-emerald-700 bg-emerald-50 border-emerald-100';
        case 'draft':
            return 'text-slate-600 bg-slate-100 border-slate-200';
        case 'scheduled':
            return 'text-amber-700 bg-amber-50 border-amber-100';
        case 'archived':
            return 'text-rose-700 bg-rose-50 border-rose-100';
        default:
            return 'text-slate-400 bg-slate-50 border-slate-100';
    }
};

const getLabel = (status: any) => {
    return typeof status === 'string' ? status : status.label || status.name;
};
</script>

<template>
    <div class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide shadow-sm border border-black/5"
        :class="getStatusClasses(status)">
        <span class="h-1.5 w-1.5 rounded-full mr-2 bg-current opacity-60"></span>
        {{ getLabel(status) }}
    </div>
</template>
