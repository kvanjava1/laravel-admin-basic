export interface Transaction {
    id: string;
    customer: string;
    date: string;
    amount: string;
    status: 'Completed' | 'Pending' | 'Cancelled';
}

export const transactions: Transaction[] = [
    { id: '#TRX-9871', customer: 'Acme Corp', date: 'Oct 24, 2023', amount: '$2,450.00', status: 'Completed' },
    { id: '#TRX-9872', customer: 'Global Tech', date: 'Oct 24, 2023', amount: '$1,200.00', status: 'Pending' },
    { id: '#TRX-9873', customer: 'Stark Ind', date: 'Oct 23, 2023', amount: '$8,900.00', status: 'Completed' },
];
