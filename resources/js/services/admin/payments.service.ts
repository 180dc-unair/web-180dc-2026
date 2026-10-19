import { getAdminData, sendAdminData } from '@/lib/admin-api';

export type AdminPaymentMethod = {
    id: string;
    name: string;
    code: string;
    gateway: 'manual' | 'midtrans';
};

export type AdminPaymentInstructions = {
    bank?: string;
    account_number?: string;
    account_name?: string;
    note?: string;
    va_number?: string;
} | null;

export type AdminPaymentOrderRef = {
    id: string;
    order_number: string;
    status: string;
    total_amount: string;
} | null;

export type AdminPayment = {
    id: string;
    order_id: string;
    status: 'pending' | 'paid' | 'failed' | 'expired' | 'cancelled';
    amount: string;
    currency: string;
    gateway: 'manual' | 'midtrans';
    payment_url: string | null;
    gateway_reference: string | null;
    instructions: AdminPaymentInstructions;
    proof: { id: string; url: string; file_id?: string; created_at?: string } | null;
    expired_at: string | null;
    paid_at: string | null;
    payment_method: AdminPaymentMethod | null;
    order?: AdminPaymentOrderRef;
    created_at: string;
    updated_at: string;
};

export type AdminPaymentListMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type AdminPaymentListPayload = {
    items: AdminPayment[];
    meta: AdminPaymentListMeta;
};

export const paymentsService = {
    list(params?: { status?: string; gateway?: string; order_number?: string; per_page?: number; page?: number }): Promise<AdminPaymentListPayload> {
        const query = new URLSearchParams();
        if (params?.status) query.set('status', params.status);
        if (params?.gateway) query.set('gateway', params.gateway);
        if (params?.order_number) query.set('order_number', params.order_number);
        if (params?.per_page) query.set('per_page', String(params.per_page));
        if (params?.page) query.set('page', String(params.page));
        const qs = query.size > 0 ? `?${query.toString()}` : '';
        return getAdminData<AdminPaymentListPayload>(`/api/admin/payments${qs}`);
    },

    show(paymentId: string): Promise<AdminPayment> {
        return getAdminData<AdminPayment>(`/api/admin/payments/${paymentId}`);
    },

    confirm(paymentId: string): Promise<AdminPayment> {
        return sendAdminData<AdminPayment>(`/api/admin/payments/${paymentId}/confirm`, 'POST');
    },
};
