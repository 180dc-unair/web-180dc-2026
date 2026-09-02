import type { ApiEnvelope, MediaAsset, MediaPurpose } from '@/types/admin';

export class ApiError extends Error {
    status: number;
    errors: Record<string, string[]>;

    constructor(message: string, status: number, errors: Record<string, string[]> = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.errors = errors;
    }
}

function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

export async function adminRequest<T>(url: string, options: RequestInit = {}): Promise<T> {
    const headers = new Headers(options.headers);
    headers.set('Accept', 'application/json');

    if (options.body && !(options.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
    }

    if (options.method && options.method !== 'GET') {
        headers.set('X-CSRF-TOKEN', csrfToken());
    }

    const response = await fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers,
    });

    if (response.status === 204) {
        return undefined as T;
    }

    const payload = (await response.json().catch(() => null)) as
        | (Partial<ApiEnvelope<T>> & { errors?: Record<string, string[]> })
        | null;

    if (!response.ok) {
        throw new ApiError(
            payload?.message ?? 'Permintaan tidak dapat diproses.',
            response.status,
            payload?.errors,
        );
    }

    return (payload?.data ?? payload) as T;
}

export function getAdminData<T>(url: string): Promise<T> {
    return adminRequest<T>(url);
}

export function sendAdminData<T>(
    url: string,
    method: 'POST' | 'PATCH' | 'PUT' | 'DELETE',
    data?: unknown,
): Promise<T> {
    return adminRequest<T>(url, {
        method,
        body: data === undefined ? undefined : JSON.stringify(data),
    });
}

export function uploadAdminMedia(file: File, purpose: MediaPurpose): Promise<MediaAsset> {
    const data = new FormData();
    data.append('file', file);
    data.append('purpose', purpose);

    return adminRequest<MediaAsset>('/api/admin/media', {
        method: 'POST',
        body: data,
    });
}
