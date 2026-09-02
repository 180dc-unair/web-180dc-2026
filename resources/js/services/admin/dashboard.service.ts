import { getAdminData } from '@/lib/admin-api';

export type DashboardSummary = {
    metrics: {
        products: { total: number; active: number; inactive: number };
        articles: { total: number; published: number; draft: number };
        services: { total: number; active: number; featured: number };
        clients: { total: number; featured: number };
        comments: { total: number; pending: number };
    };
    recent: {
        articles: Array<{ id: string; title: string; status: string; created_at: string }>;
        products: Array<{ id: string; title: string; status: string; created_at: string }>;
        comments: Array<{
            id: string;
            content: string;
            is_approved: boolean;
            user: string | null;
            article: string | null;
            created_at: string;
        }>;
    };
    activity: Array<{ date: string; label: string; articles: number; comments: number }>;
};

export const dashboardService = {
    getSummary(): Promise<DashboardSummary> {
        return getAdminData<DashboardSummary>('/api/admin/dashboard');
    },
};
