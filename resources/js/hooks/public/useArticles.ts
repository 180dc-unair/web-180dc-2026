import { useQuery } from '@tanstack/react-query';
import { articleService, type ArticleListParams } from '@/services/public/article.service';

export const articleKeys = {
    all: ['articles'] as const,
    list: (params: ArticleListParams) => [...articleKeys.all, 'list', params] as const,
    detail: (slug: string) => [...articleKeys.all, 'detail', slug] as const,
    categories: () => [...articleKeys.all, 'categories'] as const,
};

export function useArticles(params: ArticleListParams = {}) {
    return useQuery({
        queryKey: articleKeys.list(params),
        queryFn: () => articleService.list(params),
    });
}

export function useArticle(slug: string) {
    return useQuery({
        queryKey: articleKeys.detail(slug),
        queryFn: () => articleService.show(slug),
    });
}

export function useArticleCategories() {
    return useQuery({
        queryKey: articleKeys.categories(),
        queryFn: () => articleService.listCategories(),
        staleTime: 1000 * 60 * 5,
    });
}
