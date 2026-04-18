export interface Role {
    id: number;
    name: string;
    permissions: string[];
    created_at: string;
    updated_at: string;
}

export const mockupRoles: Role[] = [
    {
        id: 1,
        name: 'Administrator',
        permissions: ['all_access', 'manage_users', 'manage_roles', 'view_logs', 'system_settings'],
        created_at: '2026-01-01T08:00:00Z',
        updated_at: '2026-01-01T08:00:00Z',
    },
    {
        id: 2,
        name: 'Editor',
        permissions: ['view_users', 'edit_users', 'view_roles', 'publish_content', 'edit_content'],
        created_at: '2026-01-05T10:30:00Z',
        updated_at: '2026-01-10T14:45:00Z',
    },
    {
        id: 3,
        name: 'User',
        permissions: ['view_profile', 'edit_own_profile', 'view_content'],
        created_at: '2026-01-15T09:12:00Z',
        updated_at: '2026-01-15T09:12:00Z',
    },
    {
        id: 4,
        name: 'Guest',
        permissions: ['view_content'],
        created_at: '2026-02-01T16:20:00Z',
        updated_at: '2026-02-01T16:20:00Z',
    },
    {
        id: 5,
        name: 'Super Admin',
        permissions: ['root_access', 'all_access', 'manage_everything'],
        created_at: '2026-02-20T11:00:00Z',
        updated_at: '2026-02-21T09:30:00Z',
    },
    {
        id: 6,
        name: 'Manager',
        permissions: ['view_users', 'manage_team', 'approve_requests'],
        created_at: '2026-02-22T08:00:00Z',
        updated_at: '2026-02-22T08:00:00Z',
    },
    {
        id: 7,
        name: 'Moderator',
        permissions: ['view_users', 'ban_users', 'delete_comments'],
        created_at: '2026-02-23T09:30:00Z',
        updated_at: '2026-02-23T09:30:00Z',
    },
    {
        id: 8,
        name: 'Analyst',
        permissions: ['view_reports', 'export_data', 'view_analytics'],
        created_at: '2026-02-24T10:15:00Z',
        updated_at: '2026-02-24T10:15:00Z',
    },
    {
        id: 9,
        name: 'Developer',
        permissions: ['manage_api_keys', 'view_logs', 'system_debug'],
        created_at: '2026-02-25T14:45:00Z',
        updated_at: '2026-02-25T15:00:00Z',
    },
    {
        id: 10,
        name: 'Designer',
        permissions: ['manage_assets', 'edit_themes'],
        created_at: '2026-02-26T11:20:00Z',
        updated_at: '2026-02-26T11:20:00Z',
    },
    {
        id: 11,
        name: 'Tester',
        permissions: ['view_bugs', 'create_test_reports'],
        created_at: '2026-02-27T10:00:00Z',
        updated_at: '2026-02-27T10:00:00Z',
    },
    {
        id: 12,
        name: 'Content Creator',
        permissions: ['create_content', 'upload_media'],
        created_at: '2026-02-27T11:00:00Z',
        updated_at: '2026-02-27T11:30:00Z',
    },
];
