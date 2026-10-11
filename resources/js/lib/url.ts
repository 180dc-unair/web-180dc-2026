type QueryValue = string | number | boolean | null | undefined;

export function buildUrl(path: string, params: Record<string, QueryValue> = {}): string {
    const query = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            query.set(key, String(value));
        }
    });

    const queryString = query.toString();

    return queryString ? `${path}?${queryString}` : path;
}
