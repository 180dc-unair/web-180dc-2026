import { fetchJson } from '@/lib/api';
import { buildUrl } from '@/lib/url';
import type { ApiResponse } from '@/types';
import type { Client } from '@/types/public';

export const clientService = {
    async list(perPage: number): Promise<Client[]> {
        const response = await fetchJson<ApiResponse<Client[]>>(buildUrl('/api/clients', { per_page: perPage }));

        return response.data;
    },
};
