import { useQuery } from '@tanstack/react-query';
import { productService } from '@/services/public/product.service';

export const productKeys = {
    all: ['products'] as const,
    list: () => [...productKeys.all, 'list'] as const,
    detail: (slug: string) => [...productKeys.all, 'detail', slug] as const,
    categories: () => [...productKeys.all, 'categories'] as const,
};

export function useProducts() {
    return useQuery({
        queryKey: productKeys.list(),
        queryFn: () => productService.list(),
    });
}

export function useProduct(slug: string) {
    return useQuery({
        queryKey: productKeys.detail(slug),
        queryFn: () => productService.show(slug),
    });
}

export function useProductCategories() {
    return useQuery({
        queryKey: productKeys.categories(),
        queryFn: () => productService.listCategories(),
        staleTime: 1000 * 60 * 5,
    });
}
