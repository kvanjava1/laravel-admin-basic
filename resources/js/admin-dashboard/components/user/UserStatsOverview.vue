<script setup lang="ts">
/**
 * UserStatsOverview.vue
 * A premium, modern stats overview component for the User Management page.
 */
defineProps<{
    stats: {
        total: number;
        active: number;
        pending: number;
        banned: number;
    }
}>();

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users Card -->
        <div class="group relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-500 cursor-default">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-colors duration-500"></div>
            
            <div class="relative flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white shadow-[0_10px_20px_-5px_var(--color-primary-shadow)] group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-3xl font-light">group</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Total Network</span>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-none flex items-baseline gap-1">
                        {{ formatNumber(stats.total) }}
                        <span class="text-xs font-bold text-slate-300">Users</span>
                    </h3>
                </div>
            </div>
            
            <div class="mt-6 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full w-full opacity-20"></div>
                </div>
                <span class="text-[10px] font-black text-primary uppercase tracking-tighter">Capacity</span>
            </div>
        </div>

        <!-- Active Users Card -->
        <div class="group relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-500 cursor-default">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-colors duration-500"></div>
            
            <div class="relative flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shadow-[0_10px_20px_-5px_rgba(16,185,129,0.3)] group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-3xl font-light">verified_user</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Active Status</span>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-none flex items-baseline gap-1">
                        {{ formatNumber(stats.active) }}
                        <span class="text-[10px] font-bold py-0.5 px-2 bg-emerald-50 text-emerald-600 rounded-full ml-1">Live</span>
                    </h3>
                </div>
            </div>
            
            <div class="mt-6 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-emerald-500 rounded-full transition-all duration-1000 ease-out"
                        :style="{ width: stats.total > 0 ? (stats.active / stats.total * 100) + '%' : '0%' }"
                    ></div>
                </div>
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-tighter">{{ stats.total > 0 ? Math.round(stats.active / stats.total * 100) : 0 }}%</span>
            </div>
        </div>

        <!-- Pending Users Card -->
        <div class="group relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-500 cursor-default">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:bg-amber-500/10 transition-colors duration-500"></div>
            
            <div class="relative flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white shadow-[0_10px_20px_-5px_rgba(245,158,11,0.3)] group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-3xl font-light">pending_actions</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Queue Sync</span>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-none flex items-baseline gap-1">
                        {{ formatNumber(stats.pending) }}
                        <span class="text-xs font-bold text-amber-500/60 lowercase">awaiting</span>
                    </h3>
                </div>
            </div>
            
            <div class="mt-6 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-amber-500 rounded-full transition-all duration-1000 ease-out"
                        :style="{ width: stats.total > 0 ? (stats.pending / stats.total * 100) + '%' : '0%' }"
                    ></div>
                </div>
                <span class="text-[10px] font-black text-amber-600 uppercase tracking-tighter">{{ stats.total > 0 ? Math.round(stats.pending / stats.total * 100) : 0 }}%</span>
            </div>
        </div>

        <!-- Banned Users Card -->
        <div class="group relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-500 cursor-default">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-colors duration-500"></div>
            
            <div class="relative flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white shadow-[0_10px_20px_-5px_rgba(244,63,94,0.3)] group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-3xl font-light">block</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Restricted</span>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-none flex items-baseline gap-1">
                        {{ formatNumber(stats.banned) }}
                        <span class="material-symbols-outlined text-rose-500 text-sm">warning</span>
                    </h3>
                </div>
            </div>
            
            <div class="mt-6 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-rose-500 rounded-full transition-all duration-1000 ease-out"
                        :style="{ width: stats.total > 0 ? (stats.banned / stats.total * 100) + '%' : '0%' }"
                    ></div>
                </div>
                <span class="text-[10px] font-black text-rose-600 uppercase tracking-tighter">{{ stats.total > 0 ? Math.round(stats.banned / stats.total * 100) : 0 }}%</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
:root {
    --color-primary-shadow: rgba(var(--color-primary-rgb), 0.3);
}
</style>
