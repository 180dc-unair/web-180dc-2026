import { getAdminData, sendAdminData } from '@/lib/admin-api';

export type ArticleComment = {
    id: number;
    content: string;
    is_approved: boolean;
    user: { id: number; name: string; username: string } | null;
    article: { id: number; title: string; slug: string } | null;
    created_at: string;
};

export const commentsService = {
    list(params?: { search?: string; is_approved?: string }): Promise<ArticleComment[]> {
        const query = new URLSearchParams();
        if (params?.search) query.set('search', params.search);
        if (params?.is_approved) query.set('is_approved', params.is_approved);
        const qs = query.size > 0 ? `?${query.toString()}` : '';
        return getAdminData<ArticleComment[]>(`/api/admin/article-comments${qs}`);
    },

    moderate(commentId: number, isApproved: boolean): Promise<ArticleComment> {
        return sendAdminData<ArticleComment>(
            `/api/article-comments/${commentId}/moderate`,
            'PATCH',
            { is_approved: isApproved },
        );
    },

    remove(commentId: number): Promise<void> {
        return sendAdminData<void>(`/api/article-comments/${commentId}`, 'DELETE');
    },
};
