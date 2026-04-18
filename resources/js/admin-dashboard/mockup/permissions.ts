export interface Permission {
    id: number;
    name: string;
    slug: string;
    group: string;
}

export const mockupPermissions: Permission[] = [
    // User Management
    { id: 1, name: 'View Users', slug: 'view_users', group: 'User Management' },
    { id: 2, name: 'Create Users', slug: 'create_users', group: 'User Management' },
    { id: 3, name: 'Edit Users', slug: 'edit_users', group: 'User Management' },
    { id: 4, name: 'Delete Users', slug: 'delete_users', group: 'User Management' },

    // Role Management
    { id: 5, name: 'View Roles', slug: 'view_roles', group: 'Role Management' },
    { id: 6, name: 'Create Roles', slug: 'create_roles', group: 'Role Management' },
    { id: 7, name: 'Edit Roles', slug: 'edit_roles', group: 'Role Management' },
    { id: 8, name: 'Delete Roles', slug: 'delete_roles', group: 'Role Management' },

    // Content Management
    { id: 9, name: 'Publish Content', slug: 'publish_content', group: 'Content Management' },
    { id: 10, name: 'Edit Content', slug: 'edit_content', group: 'Content Management' },
    { id: 11, name: 'Delete Content', slug: 'delete_content', group: 'Content Management' },

    // System Settings
    { id: 12, name: 'View Logs', slug: 'view_logs', group: 'System' },
    { id: 13, name: 'System Settings', slug: 'system_settings', group: 'System' },
    { id: 14, name: 'Manage API Keys', slug: 'manage_api_keys', group: 'System' },
    { id: 15, name: 'Root Access', slug: 'root_access', group: 'System' },
];
