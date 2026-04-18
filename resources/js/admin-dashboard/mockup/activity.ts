export interface Activity {
    id: number;
    user: string;
    action: string;
    target: string;
    time: string;
    icon: string;
    iconBg: string;
}

export const activities: Activity[] = [
    { id: 1, user: 'Sarah Chen', action: 'added a new project', target: 'Project X', time: '2 mins ago', icon: 'add_task', iconBg: 'bg-blue-100 text-blue-600' },
    { id: 2, user: 'John Smith', action: 'completed milestone', target: 'Database Migration', time: '1 hour ago', icon: 'check_circle', iconBg: 'bg-green-100 text-green-600' },
    { id: 3, user: 'Emma Wilson', action: 'reported a bug', target: 'UI Glitch', time: '3 hours ago', icon: 'bug_report', iconBg: 'bg-rose-100 text-rose-600' },
    { id: 4, user: 'Mike Ross', action: 'updated documentation', target: 'API Guide', time: '5 hours ago', icon: 'description', iconBg: 'bg-amber-100 text-amber-600' },
];
