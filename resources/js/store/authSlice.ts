import { createSlice, type PayloadAction } from '@reduxjs/toolkit';
import { loadAuth } from '@/lib/auth-storage';
import type { AuthResult, AuthUser } from '@/types/public';

type AuthState = {
    user: AuthUser | null;
    token: string | null;
};

const initialState: AuthState = loadAuth() ?? { user: null, token: null };

const authSlice = createSlice({
    name: 'auth',
    initialState,
    reducers: {
        setCredentials: (_state, action: PayloadAction<AuthResult>) => ({
            user: action.payload.user,
            token: action.payload.token,
        }),
        clearCredentials: () => ({ user: null, token: null }),
    },
});

export const { setCredentials, clearCredentials } = authSlice.actions;
export default authSlice.reducer;