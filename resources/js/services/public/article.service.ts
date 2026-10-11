import { fetchJson } from '@/lib/api';
import { buildUrl } from '@/lib/url';
import type { ApiResponse } from '@/types';
import type { Article, ArticleCategory } from '@/types/public';

export type ArticleListParams = {
    category?: string;
    search?: string;
};

export const articleService = {
    async list(params: ArticleListParams = {}): Promise<Article[]> {
        const response = await fetchJson<ApiResponse<Article[]>>(buildUrl('/api/articles', params));

        return response.data;
    },

    async show(slug: string): Promise<Article> {
        const response = await fetchJson<ApiResponse<Article>>(`/api/articles/${encodeURIComponent(slug)}`);

        return response.data;
    },

    async listCategories(): Promise<ArticleCategory[]> {
        const response = await fetchJson<ApiResponse<ArticleCategory[]>>('/api/article-categories');

        return response.data;
    },
};
