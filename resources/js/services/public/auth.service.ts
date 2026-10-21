import { postJson } from '@/lib/api';
import type { ApiResponse } from '@/types';
import type { AuthResult, LoginPayload, RegisterPayload } from '@/types/public';

export const authService = {
    async login(payload: LoginPayload): Promise<AuthResult> {
        const response = await postJson<ApiResponse<AuthResult>>('/api/auth/login', payload);

        return response.data;
    },

    async register(payload: RegisterPayload): Promise<void> {
        await postJson<ApiResponse<unknown>>('/api/auth/register', payload);
    },
};