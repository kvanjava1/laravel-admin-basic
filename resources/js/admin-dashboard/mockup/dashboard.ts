export interface DashboardStats {
    label: string;
    value: string;
    trend: string;
    trendUp: boolean;
    progress: number;
    progressColor?: string;
}

export const dashboardStats: DashboardStats[] = [
    {
        label: "Total Revenue",
        value: "$45,285.00",
        trend: "+12.5%",
        trendUp: true,
        progress: 75
    },
    {
        label: "Active Users",
        value: "2,450",
        trend: "+5.2%",
        trendUp: true,
        progress: 45,
        progressColor: "#7c3aed"
    },
    {
        label: "New Orders",
        value: "156",
        trend: "-2.4%",
        trendUp: false,
        progress: 60,
        progressColor: "#0d9488"
    },
    {
        label: "Conversion Rate",
        value: "3.24%",
        trend: "+1.1%",
        trendUp: true,
        progress: 32,
        progressColor: "#f59e0b"
    },
];
