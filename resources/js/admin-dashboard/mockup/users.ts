export interface User {
    id: number;
    name: string;
    email: string;
    status: 'Active' | 'Banned' | 'Pending';
    avatar?: string;
}

export const mockUsers: User[] = [
    { id: 1, name: 'Alex Morgan', email: 'alex@example.com', status: 'Active' },
    { id: 2, name: 'Sarah Chen', email: 'sarah@example.com', status: 'Active' },
    { id: 3, name: 'John Smith', email: 'john@example.com', status: 'Pending' },
    { id: 4, name: 'Emma Wilson', email: 'emma@example.com', status: 'Banned' },
];
