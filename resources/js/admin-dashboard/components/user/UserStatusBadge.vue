<script setup lang="ts">
/**
 * UserStatusBadge.vue
 * An atomic component for displaying a stylized user status.
 */
interface Status {
    name: string;
}

defineProps<{
    status?: Status | null;
}>();
</script>

<template>
    <span 
        v-if="status"
        :class="{
            'bg-green-100 text-green-700': status.name === 'Active',
            'bg-yellow-100 text-yellow-700': status.name === 'Pending',
            'bg-rose-100 text-rose-700': status.name === 'Banned',
            'bg-slate-100 text-slate-700': status.name === 'Inactive'
        }" 
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold border border-transparent shadow-sm whitespace-nowrap"
    >
        <span 
            :class="{
                'bg-green-500': status.name === 'Active',
                'bg-yellow-600': status.name === 'Pending',
                'bg-rose-500': status.name === 'Banned',
                'bg-slate-500': status.name === 'Inactive'
            }" 
            class="w-1.5 h-1.5 rounded-full"
        ></span>
        {{ status.name }}
    </span>
    <span v-else class="text-slate-400 italic text-sm">Unknown</span>
</template>
