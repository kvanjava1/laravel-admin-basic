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
    const name = typeof status === 'string' ? status : status.name;
    const customColor = typeof status === 'object' ? status.color_class : null;

    if (customColor) return customColor;

    switch (name) {
        case 'Published': return 'text-green-600 bg-green-50';
        case 'Draft': return 'text-slate-500 bg-slate-100';
        case 'Scheduled': return 'text-blue-600 bg-blue-50';
        case 'Archived': return 'text-rose-600 bg-rose-50';
        default: return 'text-slate-400 bg-slate-50';
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
