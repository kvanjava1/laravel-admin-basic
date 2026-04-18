<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    trend?: string;
    trendUp?: boolean;
    trendType?: 'up' | 'down' | 'neutral';
    progress?: number;
    progressColor?: string;
    icon?: string;
    iconClass?: string;
}>();
</script>

<template>
    <div class="light-panel p-5 rounded-xl flex flex-col gap-4 relative overflow-hidden border border-slate-200 group hover:shadow-card-hover transition-all duration-300">
        <div class="flex justify-between items-start">
            <div class="flex items-center gap-4">
                <div v-if="icon" :class="['w-14 h-14 rounded-full flex items-center justify-center shrink-0', iconClass]">
                    <span class="material-symbols-outlined text-3xl">{{ icon }}</span>
                </div>
                <div>
                    <p class="text-text-secondary text-base font-medium">{{ label }}</p>
                    <h4 class="text-3xl font-bold text-text-primary mt-1">{{ value }}</h4>
                </div>
            </div>
            <div v-if="trend"
                class="flex items-center gap-1 px-2 py-1 rounded text-xs font-bold"
                :class="{
                    'text-teal-700 bg-teal-50 border border-teal-100': trendUp,
                    'text-rose-600 bg-rose-50 border border-rose-100': !trendUp
                }">
                <span class="material-symbols-outlined text-[14px]">{{ trendUp ? 'trending_up' : 'trending_down' }}</span>
                {{ trend }}
            </div>
        </div>
        <div v-if="progress" class="w-full bg-background-light h-1.5 rounded-full mt-auto">
            <div class="h-full rounded-full transition-all duration-500" 
                 :style="{ width: progress + '%', backgroundColor: progressColor || 'var(--color-primary)' }"
                 :class="!progressColor ? 'bg-primary' : ''"></div>
        </div>
    </div>
</template>
