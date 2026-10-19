import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ordersService, type AdminOrder } from '@/services/admin/orders.service';

export type OrdersQueryParams = {
    search?: string;
    status?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
};

export function useOrdersQuery(params: OrdersQueryParams) {
    return useQuery({
        queryKey: ['admin', 'orders', params],
        queryFn: () => ordersService.list(params),
    });
}

export function useOrderQuery(orderId: string | null) {
    return useQuery({
        queryKey: ['admin', 'order', orderId],
        queryFn: () => ordersService.show(orderId ?? ''),
        enabled: Boolean(orderId),
    });
}

export function useUpdateOrderStatus() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({ id, status }: { id: string; status: 'completed' | 'cancelled' }) =>
            ordersService.updateStatus(id, status),
        onSuccess: (updated) => {
            queryClient.setQueryData<unknown>(['admin', 'order', updated.id], updated);
            void queryClient.invalidateQueries({ queryKey: ['admin', 'orders'] });
            void queryClient.invalidateQueries({ queryKey: ['admin', 'payments'] });
            void queryClient.invalidateQueries({ queryKey: ['admin', 'dashboard'] });
        },
    });
}

export type { AdminOrder };
