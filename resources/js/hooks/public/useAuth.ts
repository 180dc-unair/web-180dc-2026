import { useMutation } from '@tanstack/react-query';
import { saveAuth } from '@/lib/auth-storage';
import { authService } from '@/services/public/auth.service';
import { setCredentials } from '@/store/authSlice';
import { useAppDispatch } from '@/store/hooks';
import type { LoginPayload, RegisterPayload } from '@/types/public';

type LoginVariables = LoginPayload & { remember: boolean };

export function useLogin() {
    const dispatch = useAppDispatch();

    return useMutation({
        mutationFn: ({ email, password }: LoginVariables) => authService.login({ email, password }),
        onSuccess: (result, variables) => {
            saveAuth(result, variables.remember);
            dispatch(setCredentials(result));
        },
    });
}

export function useRegister() {
    return useMutation({
        mutationFn: (payload: RegisterPayload) => authService.register(payload),
    });
}