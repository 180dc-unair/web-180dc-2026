import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { commentsService, type ArticleComment } from '@/services/admin/comments.service';

export function useCommentsQuery(search: string, filter: string) {
    const params: { search?: string; is_approved?: string } = {};
    const keyword = search.trim();
    if (keyword) params.search = keyword;
    if (filter !== 'all') params.is_approved = filter === 'approved' ? '1' : '0';

    return useQuery({
        queryKey: ['admin', 'comments', search, filter],
        queryFn: () => commentsService.list(params),
    });
}

export function useModerateComment() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (comment: ArticleComment) =>
            commentsService.moderate(comment.id, !comment.is_approved),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin', 'comments'] });
        },
    });
}

export function useDeleteComment() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (comment: ArticleComment) => commentsService.remove(comment.id),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'comments'] });
        },
    });
}
