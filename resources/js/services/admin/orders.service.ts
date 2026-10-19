import { getAdminData, sendAdminData } from '@/lib/admin-api';

export type AdminOrderItem = {
    id: string;
    item_type: 'product' | 'event';
    name: string;
    quantity: number;
    unit_price: string;
    total_price: string;
    product?: { id: string; slug: string; image: { id: string; url: string } | null } | null;
    event?: { id: string; slug: string; image: { id: string; url: string } | null } | null;
};

export type AdminOrderPaymentRef = {
    id: string;
    status: 'pending' | 'paid' | 'failed' | 'expired' | 'cancelled';
    gateway: 'manual' | 'midtrans';
    amount: string;
    payment_method?: { id: string; name: string; code: string; gateway: string } | null;
    created_at: string;
};

export type AdminOrder = {
    id: string;
    order_number: string;
    status: 'pending' | 'paid' | 'completed' | 'cancelled' | 'expired';
    customer_name: string;
    customer_email: string;
    notes: string | null;
    currency: string;
    subtotal_amount: string;
    total_amount: string;
    item_count: number | null;
    can_cancel: boolean;
    can_pay: boolean;
    is_expired: boolean;
    paid_at: string | null;
    expired_at: string | null;
    items?: AdminOrderItem[];
    payments?: AdminOrderPaymentRef[];
    created_at: string;
    updated_at: string;
};

export type AdminOrderListMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type AdminOrderListPayload = {
    items: AdminOrder[];
    meta: AdminOrderListMeta;
};

export const ordersService = {
    list(params?: { search?: string; status?: string; sort?: string; direction?: 'asc' | 'desc'; per_page?: number; page?: number }): Promise<AdminOrderListPayload> {
        const query = new URLSearchParams();
        if (params?.search) query.set('search', params.search);
        if (params?.status) query.set('status', params.status);
        if (params?.sort) query.set('sort', params.sort);
        if (params?.direction) query.set('direction', params.direction);
        if (params?.per_page) query.set('per_page', String(params.per_page));
        if (params?.page) query.set('page', String(params.page));
        const qs = query.size > 0 ? `?${query.toString()}` : '';
        return getAdminData<AdminOrderListPayload>(`/api/admin/orders${qs}`);
    },

    show(orderId: string): Promise<AdminOrder> {
        return getAdminData<AdminOrder>(`/api/admin/orders/${orderId}`);
    },

    updateStatus(orderId: string, status: 'completed' | 'cancelled'): Promise<AdminOrder> {
        return sendAdminData<AdminOrder>(`/api/admin/orders/${orderId}/status`, 'PATCH', { status });
    },
};
