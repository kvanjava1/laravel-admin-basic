<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import Sidebar from './Sidebar.vue';
import Header from './Header.vue';

const isSidebarOpen = ref(false);
const route = useRoute();

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const pageTitle = computed(() => {
    switch (route.name) {
        case 'dashboard':
            return 'Dashboard Overview';
        case 'users':
            return 'User Management';
        default:
            return 'Admin Dashboard';
    }
});
</script>

<template>
    <div class="h-screen bg-background-light flex font-sans overflow-hidden">
        <!-- Overlay for mobile -->
        <div v-if="isSidebarOpen" 
             @click="toggleSidebar"
             class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden backdrop-blur-sm transition-opacity duration-300">
        </div>

        <!-- Sidebar -->
        <Sidebar :class="[
                    isSidebarOpen ? 'translate-x-0 !flex' : '-translate-x-full hidden lg:flex',
                    'fixed lg:static lg:translate-x-0 z-50 transition-transform duration-300'
                 ]" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <Header :title="pageTitle" @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                <router-view />
            </main>
        </div>
    </div>
</template>

<style>
/* Global scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
