import { useQuery } from '@tanstack/react-query';
import { clientService } from '@/services/public/client.service';

export function useClients(perPage: number) {
    return useQuery({
        queryKey: ['clients', 'list', perPage],
        queryFn: () => clientService.list(perPage),
        staleTime: 1000 * 60 * 5,
    });
}
