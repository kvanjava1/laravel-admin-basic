import { createRouter, createWebHistory } from 'vue-router';
import AdminLayout from './components/layout/AdminLayout.vue';
import { useAuth } from './composables/useAuth';

/**
 * Lazy-loaded Component Declarations
 * Declaring them at the top improves readability while keeping initial bundle size small.
 */

// Auth
const LoginView = () => import('./views/auth/LoginIndex.vue');

// Dashboard
const Dashboard = () => import('./views/Dashboard.vue');

// User Management
const UserIndex = () => import('./views/user/UserIndex.vue');
const UserCreate = () => import('./views/user/UserCreate.vue');
const UserEdit = () => import('./views/user/UserEdit.vue');
const UserStatusManagement = () => import('./views/user/UserStatusManagement.vue');

// Role Management
const RoleIndex = () => import('./views/role/RoleIndex.vue');
const RoleCreate = () => import('./views/role/RoleCreate.vue');
const RoleEdit = () => import('./views/role/RoleEdit.vue');

// Profile
const ProfileEdit = () => import('./views/profile/ProfileEdit.vue');

// Category Management
const CategoryIndex = () => import('./views/category/CategoryManagement.vue');
const CategoryCreate = () => import('./views/category/CategoryCreate.vue');
const CategoryEdit = () => import('./views/category/CategoryEdit.vue');

// Media Management
const MediaIndex = () => import('./views/media/MediaIndex.vue');
const MediaCreate = () => import('./views/media/MediaCreate.vue');
const MediaEdit = () => import('./views/media/MediaEdit.vue');

// Post Management (Articles)
const ArticleIndex = () => import('./views/post/article/ArticleIndex.vue');
const ArticleCreate = () => import('./views/post/article/ArticleCreate.vue');
const ArticleEdit = () => import('./views/post/article/ArticleEdit.vue');

/**
 * Route Modules
 */

// Auth Routes
const authRoutes = [
    {
        path: '/admin/login',
        name: 'login',
        component: LoginView,
        meta: { guest: true }
    },
];

// User Management Module
const userRoutes = [
    {
        path: 'users',
        name: 'users.index',
        component: UserIndex,
        meta: { auth: true }
    },
    {
        path: 'users/create',
        name: 'users.create',
        component: UserCreate,
        meta: { auth: true }
    },
    {
        path: 'users/:id/edit',
        name: 'users.edit',
        component: UserEdit,
        meta: { auth: true }
    },
    {
        path: 'users/:id/status',
        name: 'users.status',
        component: UserStatusManagement,
        meta: { auth: true }
    },
];

// Role Management Module
const roleRoutes = [
    {
        path: 'roles',
        name: 'roles.index',
        component: RoleIndex,
        meta: { auth: true }
    },
    {
        path: 'roles/create',
        name: 'roles.create',
        component: RoleCreate,
        meta: { auth: true }
    },
    {
        path: 'roles/:id/edit',
        name: 'roles.edit',
        component: RoleEdit,
        meta: { auth: true }
    },
];

// Category Routes
const categoryRoutes = [
    {
        path: 'categories',
        name: 'categories.index',
        component: CategoryIndex,
        meta: { auth: true }
    },
    {
        path: 'categories/create',
        name: 'categories.create',
        component: CategoryCreate,
        meta: { auth: true }
    },
    {
        path: 'categories/:id/edit',
        name: 'categories.edit',
        component: CategoryEdit,
        meta: { auth: true }
    },
];

// Media Routes
const mediaRoutes = [
    {
        path: 'media',
        name: 'media.index',
        component: MediaIndex,
        meta: { auth: true }
    },
    {
        path: 'media/create',
        name: 'media.create',
        component: MediaCreate,
        meta: { auth: true }
    },
    {
        path: 'media/:id/edit',
        name: 'media.edit',
        component: MediaEdit,
        meta: { auth: true }
    },
];

// Post/Article Routes
const articleRoutes = [
    {
        path: 'articles',
        name: 'articles.index',
        component: ArticleIndex,
        meta: { auth: true }
    },
    {
        path: 'articles/create',
        name: 'articles.create',
        component: ArticleCreate,
        meta: { auth: true }
    },
    {
        path: 'articles/:id/edit',
        name: 'articles.edit',
        component: ArticleEdit,
        meta: { auth: true }
    },
];

const routes = [
    ...authRoutes,
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                component: Dashboard,
                meta: { auth: true }
            },
            ...userRoutes,
            ...roleRoutes,
            ...categoryRoutes,
            ...mediaRoutes,
            ...articleRoutes,
            {
                path: 'profile',
                name: 'profile.edit',
                component: ProfileEdit,
                meta: { auth: true }
            },
            {
                path: '',
                redirect: { name: 'dashboard' }
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

/**
 * Navigation Guards
 */
router.beforeEach((to, from, next) => {
    const { isAuthenticated } = useAuth();

    // Check if the route requires authentication
    if (to.matched.some(record => record.meta.auth)) {
        if (!isAuthenticated.value) {
            // User is not authenticated, redirect to login
            return next({ name: 'login' });
        }
    }

    // Check if the route is for guests only (like login page)
    if (to.matched.some(record => record.meta.guest)) {
        if (isAuthenticated.value) {
            // User is already authenticated, redirect to dashboard
            return next({ name: 'dashboard' });
        }
    }

    next();
});

export default router;
