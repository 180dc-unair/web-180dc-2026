import { useCallback, useMemo } from 'react';
import { router, usePage } from '@inertiajs/react';

type QueryParams = Record<string, string>;

export function useQueryParams<T extends QueryParams>(defaults: T) {
    const { url } = usePage();
    const [path, search = ''] = url.split('?');

    const params = useMemo(() => {
        const query = new URLSearchParams(search);

        return Object.fromEntries(
            Object.entries(defaults).map(([key, fallback]) => [key, query.get(key) ?? fallback]),
        ) as T;
    }, [defaults, search]);

    const setParams = useCallback(
        (next: Partial<T>) => {
            const merged: QueryParams = { ...params, ...next };
            const query = Object.fromEntries(Object.entries(merged).filter(([key, value]) => value !== defaults[key]));

            router.get(path, query, { preserveState: true, preserveScroll: true, replace: true });
        },
        [defaults, params, path],
    );

    return [params, setParams] as const;
}
