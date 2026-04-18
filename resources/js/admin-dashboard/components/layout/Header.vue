<script setup lang="ts">
defineProps<{
    title: string;
}>();

const emit = defineEmits<{
    (e: 'toggle-sidebar'): void;
}>();
</script>

<template>
    <header class="h-16 bg-white border-b border-border-light px-6 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <button @click="emit('toggle-sidebar')" class="lg:hidden text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h2 class="text-lg font-bold text-slate-800">{{ title }}</h2>
        </div>

        <div class="flex items-center gap-4">
            <button
                class="relative p-2 rounded-full hover:bg-slate-100 text-slate-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
        </div>
    </header>
</template>
