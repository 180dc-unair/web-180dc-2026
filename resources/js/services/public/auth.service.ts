import { postJson } from '@/lib/api';
import type { ApiResponse } from '@/types';
import type { AuthResult, LoginPayload } from '@/types/public';

export const authService = {
    async login(payload: LoginPayload): Promise<AuthResult> {
        const response = await postJson<ApiResponse<AuthResult>>('/api/auth/login', payload);

        return response.data;
    },
};