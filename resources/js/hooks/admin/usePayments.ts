import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { paymentsService, type AdminPayment } from '@/services/admin/payments.service';

export type PaymentsQueryParams = {
    status?: string;
    gateway?: string;
    order_number?: string;
    per_page?: number;
    page?: number;
};

export function usePaymentsQuery(params: PaymentsQueryParams) {
    return useQuery({
        queryKey: ['admin', 'payments', params],
        queryFn: () => paymentsService.list(params),
    });
}

export function usePaymentQuery(paymentId: string | null) {
    return useQuery({
        queryKey: ['admin', 'payment', paymentId],
        queryFn: () => paymentsService.show(paymentId ?? ''),
        enabled: Boolean(paymentId),
    });
}

export function useConfirmPayment() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (paymentId: string) => paymentsService.confirm(paymentId),
        onSuccess: (updated) => {
            queryClient.setQueryData<unknown>(['admin', 'payment', updated.id], updated);
            void queryClient.invalidateQueries({ queryKey: ['admin', 'payments'] });
            void queryClient.invalidateQueries({ queryKey: ['admin', 'orders'] });
            void queryClient.invalidateQueries({ queryKey: ['admin', 'dashboard'] });
        },
    });
}

export type { AdminPayment };
