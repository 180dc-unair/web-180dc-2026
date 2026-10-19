import type { AuthResult } from '@/types/public';

const STORAGE_KEY = 'auth';

function read(storage: Storage): AuthResult | null {
    try {
        const raw = storage.getItem(STORAGE_KEY);

        return raw ? (JSON.parse(raw) as AuthResult) : null;
    } catch {
        return null;
    }
}

export function loadAuth(): AuthResult | null {
    if (typeof window === 'undefined') {
        return null;
    }

    return read(window.localStorage) ?? read(window.sessionStorage);
}

export function clearAuth(): void {
    try {
        window.localStorage.removeItem(STORAGE_KEY);
        window.sessionStorage.removeItem(STORAGE_KEY);
    } catch {
        // Storage tidak tersedia, abaikan.
    }
}

export function saveAuth(auth: AuthResult, remember: boolean): void {
    clearAuth();

    try {
        (remember ? window.localStorage : window.sessionStorage).setItem(STORAGE_KEY, JSON.stringify(auth));
    } catch {
        // Storage tidak tersedia, sesi hanya bertahan di memori.
    }
}