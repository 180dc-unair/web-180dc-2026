import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { CategoryBadge } from '@/components/public/CategoryBadge';
import { EmptyState } from '@/components/public/EmptyState';
import { Container } from '@/components/public/layout/Container';
import { PublicLayout } from '@/components/public/layout/PublicLayout';
import { PageBreadcrumb } from '@/components/public/PageBreadcrumb';
import { ProductCard } from '@/components/public/store/ProductCard';
import { ProductGallery } from '@/components/public/store/ProductGallery';
import { ProductInfoTabs } from '@/components/public/store/ProductInfoTabs';
import { ProductSpecCard } from '@/components/public/store/ProductSpecCard';
import { QuantityStepper } from '@/components/public/store/QuantityStepper';
import { Button, buttonVariants } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { SIGN_IN_LINK } from '@/contants/public/navigation';
import {
    MAX_ORDER_QUANTITY,
    PRODUCT_DETAIL_COPY,
    PRODUCT_PURCHASE_NOTES,
    RELATED_PRODUCTS_LIMIT,
} from '@/contants/public/store';
import { useProduct, useProducts } from '@/hooks/public/useProducts';
import { formatPrice } from '@/lib/format';
import { sanitizeHtml } from '@/lib/html';
import { pickRelated } from '@/lib/related';
import { cn } from '@/lib/utils';
import type { Product } from '@/types/public';

type ProductShowProps = {
    slug: string;
};

const buyButtonClassName = 'h-14 flex-1 rounded-sm text-base font-bold text-white hover:bg-primary/90';

function getMaxQuantity(product: Product): number {
    return product.type === 'physical' ? Math.min(product.stock, MAX_ORDER_QUANTITY) : MAX_ORDER_QUANTITY;
}

function ProductDetailSkeleton() {
    return (
        <Container className="flex flex-col gap-8 pt-10 pb-18">
            <Skeleton className="h-5 w-56" />
            <div className="flex flex-col gap-10 lg:flex-row lg:gap-14">
                <Skeleton className="h-80 w-full sm:h-105 lg:w-137" />
                <div className="flex flex-1 flex-col gap-4">
                    <Skeleton className="h-6 w-28" />
                    <Skeleton className="h-10 w-full" />
                    <Skeleton className="h-10 w-2/3" />
                    <Skeleton className="h-11 w-48" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-5/6" />
                    <Skeleton className="h-14 w-full" />
                </div>
            </div>
        </Container>
    );
}

function PurchaseActions({ product }: { product: Product }) {
    const [quantity, setQuantity] = useState(1);
    const maxQuantity = getMaxQuantity(product);

    if (maxQuantity < 1) {
        return (
            <Button disabled className={cn(buyButtonClassName, 'w-full')}>
                Stok habis
            </Button>
        );
    }

    return (
        <div className="flex w-full items-center gap-3 py-2">
            <QuantityStepper value={quantity} max={maxQuantity} onChange={setQuantity} />
            <Link href={SIGN_IN_LINK.href} className={cn(buttonVariants(), buyButtonClassName)}>
                {PRODUCT_DETAIL_COPY.buy}
            </Link>
        </div>
    );
}

export default function ProductShow({ slug }: ProductShowProps) {
    const productQuery = useProduct(slug);
    const productsQuery = useProducts();
    const product = productQuery.data;

    const descriptionHtml = useMemo(() => (product?.description ? sanitizeHtml(product.description) : ''), [product]);
    const relatedProducts = useMemo(
        () =>
            product
                ? pickRelated(
                      productsQuery.data ?? [],
                      product,
                      (item) => item.category?.id === product.category?.id,
                      RELATED_PRODUCTS_LIMIT,
                  )
                : [],
        [product, productsQuery.data],
    );

    return (
        <PublicLayout title={product?.title ?? 'Product'}>
            {productQuery.isPending && <ProductDetailSkeleton />}

            {productQuery.isError && (
                <Container className="py-20">
                    <EmptyState
                        title="Product unavailable"
                        description={productQuery.error.message}
                        actions={
                            <>
                                <Link href="/store" className={buttonVariants({ variant: 'outline' })}>
                                    Back to store
                                </Link>
                                <Button onClick={() => productQuery.refetch()} className="text-white">
                                    Try again
                                </Button>
                            </>
                        }
                    />
                </Container>
            )}

            {product && (
                <>
                    <Container className="flex flex-col gap-8 pt-10 pb-18">
                        <PageBreadcrumb
                            items={[{ label: 'Home', href: '/' }, { label: 'Store', href: '/store' }, { label: product.title }]}
                        />

                        <div className="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-14">
                            <div className="lg:w-137 lg:shrink-0">
                                <ProductGallery product={product} />
                            </div>

                            <div className="flex min-w-0 flex-1 flex-col items-start gap-4">
                                {product.category && (
                                    <CategoryBadge tone="secondary" size="sm" uppercase>
                                        {product.category.name}
                                    </CategoryBadge>
                                )}
                                <h1 className="text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl sm:leading-11.5">
                                    {product.title}
                                </h1>
                                <p className="py-1 text-[34px] leading-11 font-extrabold text-accent-foreground">
                                    {formatPrice(product.price)}
                                </p>
                                {product.short_description && (
                                    <p className="text-[15px] leading-6.5 text-muted-foreground">{product.short_description}</p>
                                )}
                                <PurchaseActions product={product} />
                                <p className="text-xs leading-5 text-muted-foreground/85">
                                    {PRODUCT_PURCHASE_NOTES[product.type]?.join(' • ')}
                                </p>
                            </div>
                        </div>
                    </Container>

                    <Container className="flex flex-col gap-12 pb-18 lg:flex-row lg:items-start">
                        <ProductInfoTabs descriptionHtml={descriptionHtml} className="min-w-0 flex-1" />
                        <ProductSpecCard product={product} className="lg:mt-20 lg:w-86 lg:shrink-0" />
                    </Container>

                    {relatedProducts.length > 0 && (
                        <section aria-labelledby="related-products-title" className="bg-primary/5">
                            <Container className="flex flex-col gap-7 pt-16 pb-22">
                                <h2
                                    id="related-products-title"
                                    className="text-[32px] leading-10.5 font-extrabold text-foreground"
                                >
                                    {PRODUCT_DETAIL_COPY.relatedTitle}
                                </h2>
                                <div className="grid items-start gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                    {relatedProducts.map((related) => (
                                        <ProductCard key={related.id} product={related} />
                                    ))}
                                </div>
                            </Container>
                        </section>
                    )}
                </>
            )}
        </PublicLayout>
    );
}
