import type { AdminRecord } from '@/types/admin';
import type { PaginatedResponse } from '@/types/pagination';
import { getAdminData, sendAdminData } from '@/lib/admin-api';

export const resourceService = {
    list(endpoint: string, params?: Record<string, string>): Promise<AdminRecord[] | PaginatedResponse<AdminRecord>> {
        const query = params ? `?${new URLSearchParams(params).toString()}` : '';
        return getAdminData(endpoint + query);
    },

    show(endpoint: string, id: string): Promise<AdminRecord> {
        return getAdminData(`${endpoint}/${id}`);
    },

    create(endpoint: string, payload: Record<string, unknown>): Promise<AdminRecord> {
        return sendAdminData<AdminRecord>(endpoint, 'POST', payload);
    },

    update(endpoint: string, id: string, payload: Record<string, unknown>): Promise<AdminRecord> {
        return sendAdminData<AdminRecord>(`${endpoint}/${id}`, 'PATCH', payload);
    },

    toggle(endpoint: string, id: string, field: string): Promise<AdminRecord> {
        return sendAdminData<AdminRecord>(`${endpoint}/${id}/toggle`, 'PATCH', { field });
    },

    remove(endpoint: string, id: string): Promise<void> {
        return sendAdminData<void>(`${endpoint}/${id}`, 'DELETE');
    },

    fetchRelation(relationUrl: string): Promise<AdminRecord[]> {
        return getAdminData<AdminRecord[]>(relationUrl);
    },
};
