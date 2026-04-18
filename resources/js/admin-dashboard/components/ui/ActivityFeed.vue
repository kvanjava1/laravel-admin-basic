<script setup lang="ts">
interface Activity {
    id: number;
    user: string;
    action: string;
    target: string;
    time: string;
    icon: string;
    iconBg: string;
}

import { activities } from '../../mockup/activity';
import { alertService } from '../../utils/sweetalert';

const handleViewAll = () => {
    alertService.alert({
        title: 'Activity Log',
        text: 'Loading full activity log...',
        icon: 'info'
    });
};
</script>

<template>
    <div class="light-panel rounded-xl p-6">
        <h3 class="text-lg font-bold text-text-primary mb-6">Recent Activity</h3>
        <div class="space-y-6">
            <div v-for="item in activities" :key="item.id" class="flex gap-4 group cursor-pointer">
                <div :class="[item.iconBg, 'h-10 w-10 shrink-0 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110']">
                    <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                </div>
                <div class="flex flex-col">
                    <p class="text-sm text-text-secondary">
                        <span class="font-bold text-text-primary">{{ item.user }}</span>
                        {{ item.action }}
                        <span class="font-semibold text-primary">{{ item.target }}</span>
                    </p>
                    <span class="text-xs text-text-secondary/60 mt-1">{{ item.time }}</span>
                </div>
            </div>
        </div>
        <button @click="handleViewAll" 
                class="w-full mt-8 py-3 rounded-xl border border-dashed border-border-light text-text-secondary font-bold text-sm hover:border-primary hover:text-primary transition-all active:scale-95">
            View All Activity
        </button>
    </div>
</template>
