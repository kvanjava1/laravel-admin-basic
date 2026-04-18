import { createApp } from 'vue';
import router from './router';
import App from './App.vue';

/**
 * Initialize the Admin Dashboard Vue Application
 */
export function initAdminDashboard(el: string | HTMLElement) {
    const app = createApp(App);

    app.use(router);

    app.mount(el);
    return app;
}
