import { PRODUCT_TYPE_LABELS } from '@/contants/public/store';
import type { Product } from '@/types/public';
import { CategoryBadge } from '../CategoryBadge';

export function ProductGallery({ product }: { product: Product }) {
    return (
        <div className="relative h-80 overflow-hidden rounded-md border border-muted-foreground/20 bg-muted-foreground/10 px-6 py-12 sm:h-105 sm:px-11 sm:py-12.5">
            {product.image && (
                <img
                    src={product.image.url}
                    alt={product.title}
                    fetchPriority="high"
                    className="size-full rounded-sm object-cover"
                />
            )}
            <CategoryBadge size="sm" uppercase className="absolute top-4.75 left-4.75">
                {PRODUCT_TYPE_LABELS[product.type] ?? product.type}
            </CategoryBadge>
        </div>
    );
}
