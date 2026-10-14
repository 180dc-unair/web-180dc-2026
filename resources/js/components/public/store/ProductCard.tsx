import { Link } from '@inertiajs/react';
import { Skeleton } from '@/components/ui/skeleton';
import { PRODUCT_TYPE_LABELS } from '@/contants/public/store';
import { formatPrice } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Product } from '@/types/public';
import { CategoryBadge } from '../CategoryBadge';

const cardClassName = 'flex flex-col overflow-hidden rounded-md border border-muted-foreground/20 bg-card';

export function ProductCard({ product }: { product: Product }) {
    return (
        <Link
            href={`/store/${product.slug}`}
            className={cn(
                cardClassName,
                'group outline-none transition-shadow hover:shadow-lg focus-visible:ring-3 focus-visible:ring-ring/50',
            )}
        >
            <article>
                <div className="relative h-52 bg-primary/10 p-6">
                    <div className="size-full overflow-hidden rounded-sm bg-muted shadow-xl">
                        {product.image && (
                            <img
                                src={product.image.url}
                                alt=""
                                loading="lazy"
                                decoding="async"
                                className="size-full object-cover"
                            />
                        )}
                    </div>
                    <CategoryBadge size="sm" className="absolute top-4 left-4">
                        {PRODUCT_TYPE_LABELS[product.type] ?? product.type}
                    </CategoryBadge>
                </div>
                <div className="flex flex-col gap-1.5 p-6">
                    {product.category && (
                        <p className="text-xs leading-4.5 font-medium text-muted-foreground/85">{product.category.name}</p>
                    )}
                    <h3 className="leading-6 font-semibold text-foreground transition-colors group-hover:text-primary">
                        {product.title}
                    </h3>
                    <p className="text-lg leading-6.5 font-bold text-primary">{formatPrice(product.price)}</p>
                </div>
            </article>
        </Link>
    );
}

export function ProductCardSkeleton() {
    return (
        <div className={cardClassName}>
            <div className="h-52 bg-primary/10 p-6">
                <Skeleton className="size-full rounded-sm" />
            </div>
            <div className="flex flex-col gap-2.5 p-6">
                <Skeleton className="h-3 w-1/3" />
                <Skeleton className="h-4 w-4/5" />
                <Skeleton className="h-5 w-1/2" />
            </div>
        </div>
    );
}
