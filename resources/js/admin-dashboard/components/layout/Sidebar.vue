<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../composables/useAuth';
import { usePermission } from '../../composables/usePermission';
import { authService } from '../../services/authService';

const router = useRouter();
const { user } = useAuth();
const { can } = usePermission();

const menuItems = [
    { name: 'Dashboard', icon: 'dashboard', to: { name: 'dashboard' } },
    { name: 'User Management', icon: 'group', to: { name: 'users.index' }, permission: 'view-users' },
    { name: 'Role Management', icon: 'shield_person', to: { name: 'roles.index' }, permission: 'view-roles' },
    { name: 'Category Management', icon: 'category', to: { name: 'categories.index' }, permission: 'view-categories' },
    { name: 'Media Management', icon: 'image', to: { name: 'media.index' }, permission: 'view-media' },
];

const filteredMenuItems = computed(() => menuItems.filter(item => !item.permission || can(item.permission)));

const dropdowns = ref({
    catalog: false,
    inventory: false,
    posts: false, // Closed by default
});

const toggleDropdown = (key: 'catalog' | 'inventory' | 'posts') => {
    if (key === 'catalog') {
        dropdowns.value.catalog = !dropdowns.value.catalog;
        if (dropdowns.value.catalog) {
            dropdowns.value.inventory = false;
            dropdowns.value.posts = false;
        }
    } else if (key === 'posts') {
        dropdowns.value.posts = !dropdowns.value.posts;
        if (dropdowns.value.posts) {
            dropdowns.value.catalog = false;
        }
    } else {
        dropdowns.value.inventory = !dropdowns.value.inventory;
    }
};

const handleLogout = async () => {
    try {
        await authService.logout();
        router.push({ name: 'login' });
    } catch (error) {
        console.error('Logout failed', error);
        // Even if API logout fails, we usually want to clear local state and redirect
        router.push({ name: 'login' });
    }
};
</script>

<template>
    <!-- Sidebar (Full Height) -->
    <aside id="sidebar"
        class="h-full w-72 bg-sidebar-bg border-r border-white/5 shadow-sm flex flex-col shrink-0">
        <div class="p-6 flex items-center gap-3 border-b border-white/5">
            <div
                class="h-8 w-8 rounded bg-primary flex items-center justify-center text-white font-bold shadow-md shadow-primary/30">
                A</div>
            <h1 class="text-xl font-bold tracking-tight text-white">Admin<span class="text-primary">Dash</span></h1>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-3 flex flex-col gap-1">
            <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">General</p>
            <router-link v-for="item in filteredMenuItems" :key="item.name"
                :to="item.to"
                custom
                v-slot="{ navigate, href, isActive, isExactActive }">
                <a :href="href" @click="navigate"
                    :class="[
                        isActive || isExactActive 
                        ? 'bg-primary text-white shadow-md shadow-primary/20' 
                        : 'text-slate-400 hover:text-white hover:bg-white/5',
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all active:scale-95'
                    ]">
                    <span class="material-symbols-outlined text-[22px]">{{ item.icon }}</span>
                    <span class="font-medium">{{ item.name }}</span>
                </a>
            </router-link>

            <!-- Post Management -->
            <div v-if="can('view-articles')" class="mt-4">
                <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Content</p>
                <div class="flex flex-col gap-1">
                    <button @click="toggleDropdown('posts')"
                        :class="[
                            dropdowns.posts 
                            ? 'text-white bg-white/5' 
                            : 'text-slate-400 hover:text-white hover:bg-white/5',
                            'flex items-center justify-between px-3 py-2.5 rounded-lg transition-all w-full group active:scale-95'
                        ]">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[22px]" :class="dropdowns.posts ? 'text-primary' : 'text-inherit'">dynamic_feed</span>
                            <span class="font-medium">Post Management</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform"
                            :class="{ 'rotate-90': dropdowns.posts }">chevron_right</span>
                    </button>

                    <div v-show="dropdowns.posts" class="ml-4 pl-4 border-l border-white/10 flex flex-col gap-1 mt-1">
                        <router-link :to="{ name: 'articles.index' }" v-slot="{ isActive, navigate, href }">
                            <a :href="href" @click="navigate"
                                :class="[
                                    isActive ? 'text-white bg-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5',
                                    'px-3 py-2 font-medium rounded-md transition-all flex items-center gap-2'
                                ]">
                                <span class="material-symbols-outlined text-sm">article</span>
                                Articles
                            </a>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Multi-level Menu Example (Hidden by user request, code preserved) -->
            <div v-if="false" class="mt-4">
                <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Catalog</p>

                <div class="flex flex-col gap-1">
                    <button @click="toggleDropdown('catalog')"
                        :class="[
                            dropdowns.catalog 
                            ? 'text-white bg-white/5' 
                            : 'text-slate-400 hover:text-white hover:bg-white/5',
                            'flex items-center justify-between px-3 py-2.5 rounded-lg transition-all w-full group active:scale-95'
                        ]">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[22px]" :class="dropdowns.catalog ? 'text-primary' : 'text-inherit'">inventory_2</span>
                            <span class="font-medium">Product Catalog</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform"
                            :class="{ 'rotate-90': dropdowns.catalog }">chevron_right</span>
                    </button>

                    <div v-show="dropdowns.catalog" class="ml-4 pl-4 border-l border-white/10 flex flex-col gap-1 mt-1">
                        <div class="flex flex-col gap-1">
                            <button @click="toggleDropdown('inventory')"
                                class="flex items-center justify-between px-3 py-2 rounded-md text-slate-400 hover:text-white hover:bg-white/5 transition-all w-full group/sub active:scale-95">
                                <span class="font-medium">Inventory</span>
                                <span class="material-symbols-outlined text-sm transition-transform"
                                    :class="{ 'rotate-180': dropdowns.inventory }">expand_more</span>
                            </button>

                            <div v-show="dropdowns.inventory" class="ml-2 pl-4 border-l border-white/5 flex flex-col gap-1 mt-1">
                                <a class="px-3 py-1.5 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-md transition-all" href="#">
                                    Warehouse Alpha
                                </a>
                                <a class="px-3 py-1.5 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-md transition-all" href="#">
                                    Warehouse Beta
                                </a>
                            </div>
                        </div>

                        <a class="px-3 py-2 font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-md transition-all"
                            href="#">
                            Categories
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">System</p>
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-all"
                    href="#">
                    <span class="material-symbols-outlined text-[22px]">settings</span>
                    <span class="font-medium">Preferences</span>
                </a>
                <button 
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:underline hover:text-red-400 transition-all mt-2 active:scale-95"
                >
                    <span class="material-symbols-outlined text-[22px]">logout</span>
                    <span class="font-medium">Logout</span>
                </button>
            </div>
        </div>

        <router-link 
            :to="{ name: 'profile.edit' }"
            class="block p-4 bg-white/5 border-t border-white/5 hover:bg-white/10 transition-colors group"
        >
            <div
                class="flex items-center gap-3 p-2 rounded-xl bg-slate-800/50 border border-white/10 group-hover:border-white/20 transition-all">
                <div class="relative">
                    <div class="h-9 w-9 rounded-lg bg-slate-700 border border-white/10 overflow-hidden flex items-center justify-center">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" class="h-full w-full object-cover" />
                        <span v-else class="material-symbols-outlined text-white">person</span>
                    </div>
                    <div class="absolute -top-1 -right-1 h-3 w-3 bg-green-500 rounded-full border-2 border-slate-900">
                    </div>
                </div>
                <div class="flex flex-col overflow-hidden">
                    <span class="text-sm font-semibold text-white truncate group-hover:text-primary transition-colors">
                        {{ user?.name || 'Loading...' }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">
                        {{ user?.roles?.[0]?.name || 'Admin' }}
                    </span>
                </div>
            </div>
        </router-link>
    </aside>
</template>
