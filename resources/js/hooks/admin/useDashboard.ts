import { useQuery } from '@tanstack/react-query';
import { dashboardService, type DashboardSummary } from '@/services/admin/dashboard.service';

export function useDashboardSummary() {
    return useQuery({
        queryKey: ['admin', 'dashboard'],
        queryFn: () => dashboardService.getSummary(),
    });
}

export type { DashboardSummary };
