import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { AdminRecord, ResourceConfig } from '@/types/admin';
import { resourceService } from '@/services/admin/resource.service';
import type { ApiError } from '@/lib/admin-api';

type FormValue = string | number | boolean | null;

export function useCreateResource(config: ResourceConfig) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (payload: Record<string, FormValue>) =>
            resourceService.create(config.endpoint, payload),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
        },
    });
}

export function useUpdateResource(config: ResourceConfig) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({ id, payload }: { id: string; payload: Record<string, FormValue> }) =>
            resourceService.update(config.endpoint, id, payload),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
        },
    });
}

export function useDeleteResource(config: ResourceConfig) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (record: AdminRecord) => resourceService.remove(config.endpoint, record.id),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
        },
    });
}

export function useToggleResource(config: ResourceConfig) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({ record, field }: { record: AdminRecord; field: string }) =>
            resourceService.toggle(config.endpoint, record.id, field),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
        },
    });
}

export function useRelationData(relationUrl: string | undefined) {
    return useQuery({
        queryKey: ['admin', 'relation', relationUrl],
        queryFn: () => resourceService.fetchRelation(relationUrl!),
        enabled: Boolean(relationUrl),
        staleTime: 60_000,
    });
}

export type { ApiError, FormValue };
