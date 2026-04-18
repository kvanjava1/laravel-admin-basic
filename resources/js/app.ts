import '../css/app.css';
import './bootstrap';

import { initAdminDashboard } from './admin-dashboard';

// Initialize the app on the #app element
const rootElement = document.getElementById('app');

if (rootElement) {
    initAdminDashboard(rootElement);
}
