<script setup lang="ts">
/**
 * TablePagination.vue
 * A reusable pagination footer for tables.
 */
const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    lastPage: number;
    from: number;
    to: number;
}>();

const emit = defineEmits<{
    (e: 'on-change', page: number): void;
}>();

const changePage = (page: number) => {
    if (page >= 1 && page <= props.lastPage) {
        emit('on-change', page);
    }
};
</script>

<template>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Record Summary -->
        <p class="text-base text-text-secondary font-medium tracking-wide">
            Showing <span class="text-text-primary font-bold">{{ from }}</span> to <span class="text-text-primary font-bold">{{ to }}</span>
            of <span class="text-text-primary font-bold">{{ total }}</span> entries
        </p>

        <!-- Pagination Buttons -->
        <div class="flex items-center gap-3">
            <!-- Prev Button -->
            <button 
                :disabled="currentPage === 1"
                @click="changePage(currentPage - 1)"
                class="px-3 py-1.5 rounded-lg border border-border-light text-text-secondary flex items-center gap-1 transition-all"
                :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-primary hover:border-primary hover:bg-primary/5'"
            >
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                <span class="text-xs font-bold uppercase tracking-wider">Prev</span>
            </button>

            <!-- Page Numbers (Simple logic for now) -->
            <div class="flex items-center gap-1.5">
                <button 
                    v-for="page in lastPage" 
                    :key="page"
                    @click="changePage(page)"
                    class="w-8 h-8 rounded-lg text-sm font-bold transition-all"
                    :class="page === currentPage 
                        ? 'bg-primary text-white shadow-md shadow-primary/20' 
                        : 'border border-border-light text-text-secondary hover:text-primary hover:border-primary hover:bg-primary/5'"
                >
                    {{ page }}
                </button>
            </div>

            <!-- Next Button -->
            <button 
                :disabled="currentPage === lastPage"
                @click="changePage(currentPage + 1)"
                class="px-3 py-1.5 rounded-lg border border-border-light text-text-secondary flex items-center gap-1 group transition-all"
                :class="currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-primary hover:border-primary hover:bg-primary/5'"
            >
                <span class="text-xs font-bold uppercase tracking-wider">Next</span>
                <span class="material-symbols-outlined text-[20px] group-hover:translate-x-0.5 transition-transform">chevron_right</span>
            </button>
        </div>
    </div>
</template>
