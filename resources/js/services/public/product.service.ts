import { fetchJson } from '@/lib/api';
import type { ApiResponse } from '@/types';
import type { Product, ProductCategory } from '@/types/public';

export const productService = {
    async list(): Promise<Product[]> {
        const response = await fetchJson<ApiResponse<Product[]>>('/api/products');

        return response.data;
    },

    async show(slug: string): Promise<Product> {
        const response = await fetchJson<ApiResponse<Product>>(`/api/products/${encodeURIComponent(slug)}`);

        return response.data;
    },

    async listCategories(): Promise<ProductCategory[]> {
        const response = await fetchJson<ApiResponse<ProductCategory[]>>('/api/product-categories');

        return response.data;
    },
};
