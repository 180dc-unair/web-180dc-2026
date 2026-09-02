import { useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import type { AdminRecord, ResourceConfig } from '@/types/admin';
import type { PaginatedResponse, PaginationMeta } from '@/types/pagination';
import { resourceService } from '@/services/admin/resource.service';

type ResourceQueryResult = {
    rows: AdminRecord[];
    meta: PaginationMeta | null;
    isLoading: boolean;
    isError: boolean;
    isFetching: boolean;
    isSuccess: boolean;
    refetch: () => void;
};

export function useResourceQuery(
    config: ResourceConfig,
    search: string,
    page: number,
): ResourceQueryResult {
    const params = useMemo(() => {
        const p: Record<string, string> = {};
        const keyword = search.trim();
        if (keyword) p.search = keyword;
        p.page = String(page);
        return p;
    }, [search, page]);

    const query = useQuery({
        queryKey: ['admin', 'resource', config.key, search, page],
        queryFn: () => resourceService.list(config.endpoint, params),
    });

    const { rows, meta } = useMemo(() => {
        const raw = query.data;
        if (Array.isArray(raw)) {
            return { rows: raw, meta: null };
        }
        if (raw && typeof raw === 'object' && 'data' in raw) {
            const paginated = raw as PaginatedResponse<AdminRecord>;
            return { rows: paginated.data, meta: paginated.meta ?? null };
        }
        return { rows: [], meta: null };
    }, [query.data]);

    return {
        rows,
        meta,
        isLoading: query.isLoading,
        isError: query.isError,
        isFetching: query.isFetching,
        isSuccess: query.isSuccess,
        refetch: () => query.refetch(),
    };
}
