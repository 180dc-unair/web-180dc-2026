import { useMemo, useRef, type ComponentProps } from 'react';
import { EmptyState } from '@/components/public/EmptyState';
import { FilterChips } from '@/components/public/FilterChips';
import { Container } from '@/components/public/layout/Container';
import { PublicLayout } from '@/components/public/layout/PublicLayout';
import { PageHero } from '@/components/public/PageHero';
import { PaginationNav } from '@/components/public/PaginationNav';
import { SortSelect } from '@/components/public/SortSelect';
import { PartnerLogos } from '@/components/public/store/PartnerLogos';
import { ProductCard, ProductCardSkeleton } from '@/components/public/store/ProductCard';
import { Button } from '@/components/ui/button';
import { ALL_FILTER_OPTION } from '@/contants/public/filters';
import { PRODUCT_SORT_OPTIONS, PRODUCT_TYPE_OPTIONS, PRODUCTS_PER_PAGE, STORE_HERO } from '@/contants/public/store';
import { useProductCategories, useProducts } from '@/hooks/public/useProducts';
import { useQueryParams } from '@/hooks/public/useQueryParams';
import { paginate } from '@/lib/pagination';
import type { Product } from '@/types/public';

const DEFAULT_FILTERS = {
    type: ALL_FILTER_OPTION.value,
    category: ALL_FILTER_OPTION.value,
    sort: PRODUCT_SORT_OPTIONS[0].value,
    page: '1',
};

const productComparators: Record<string, (a: Product, b: Product) => number> = {
    'price-asc': (a, b) => Number(a.price) - Number(b.price),
    'price-desc': (a, b) => Number(b.price) - Number(a.price),
    'best-selling': (a, b) => b.sold_count - a.sold_count,
};

function selectProducts(products: Product[], filters: typeof DEFAULT_FILTERS): Product[] {
    const matches = products.filter(
        (product) =>
            (filters.type === ALL_FILTER_OPTION.value || product.type === filters.type) &&
            (filters.category === ALL_FILTER_OPTION.value || product.category?.slug === filters.category),
    );
    const comparator = productComparators[filters.sort];

    return comparator ? [...matches].sort(comparator) : matches;
}

function LabeledFilter({ label, ...props }: ComponentProps<typeof FilterChips>) {
    return (
        <div className="flex min-w-0 flex-col gap-2 md:flex-row md:items-center md:gap-3">
            <span aria-hidden="true" className="text-xs leading-5 font-bold tracking-[1.2px] text-muted-foreground/85 uppercase">
                {label}
            </span>
            <FilterChips label={label} {...props} />
        </div>
    );
}

export default function StoreIndex() {
    const listRef = useRef<HTMLDivElement>(null);
    const [filters, setFilters] = useQueryParams(DEFAULT_FILTERS);

    const productsQuery = useProducts();
    const categoriesQuery = useProductCategories();

    const categoryOptions = useMemo(
        () => [ALL_FILTER_OPTION, ...(categoriesQuery.data ?? []).map((item) => ({ label: item.name, value: item.slug }))],
        [categoriesQuery.data],
    );

    const products = useMemo(() => selectProducts(productsQuery.data ?? [], filters), [productsQuery.data, filters]);

    const totalPages = Math.max(1, Math.ceil(products.length / PRODUCTS_PER_PAGE));
    const currentPage = Math.min(Math.max(Number(filters.page) || 1, 1), totalPages);
    const visibleProducts = paginate(products, currentPage, PRODUCTS_PER_PAGE);

    const changePage = (page: number) => {
        setFilters({ page: String(page) });
        listRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    return (
        <PublicLayout title="Store">
            <PageHero {...STORE_HERO} tone="secondary" />

            <Container className="flex flex-col gap-4.5 pt-9 pb-7">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <LabeledFilter
                        label="Type"
                        options={PRODUCT_TYPE_OPTIONS}
                        value={filters.type}
                        onChange={(value) => setFilters({ type: value, page: DEFAULT_FILTERS.page })}
                    />
                    <SortSelect
                        options={PRODUCT_SORT_OPTIONS}
                        value={filters.sort}
                        onChange={(value) => setFilters({ sort: value, page: DEFAULT_FILTERS.page })}
                    />
                </div>
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <LabeledFilter
                        label="Category"
                        options={categoryOptions}
                        value={filters.category}
                        onChange={(value) => setFilters({ category: value, page: DEFAULT_FILTERS.page })}
                    />
                    {productsQuery.isSuccess && (
                        <p className="shrink-0 text-sm leading-5 font-medium text-muted-foreground/85">
                            {products.length} {products.length === 1 ? 'product' : 'products'}
                        </p>
                    )}
                </div>
            </Container>

            <Container ref={listRef} className="flex scroll-mt-36 flex-col gap-14 pb-18">
                {productsQuery.isPending && (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {Array.from({ length: 8 }, (_, index) => (
                            <ProductCardSkeleton key={index} />
                        ))}
                    </div>
                )}

                {productsQuery.isError && (
                    <EmptyState
                        title="Products could not be loaded"
                        description="Something went wrong while fetching products. Please try again."
                        actions={
                            <Button variant="outline" onClick={() => productsQuery.refetch()}>
                                Try again
                            </Button>
                        }
                    />
                )}

                {productsQuery.isSuccess && products.length === 0 && (
                    <EmptyState
                        title="No products found"
                        description="There are no products matching these filters. Try another type or category."
                    />
                )}

                {visibleProducts.length > 0 && (
                    <>
                        <div className="grid items-start gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            {visibleProducts.map((product) => (
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                        <PaginationNav currentPage={currentPage} totalPages={totalPages} onPageChange={changePage} />
                    </>
                )}
            </Container>

            <PartnerLogos />
        </PublicLayout>
    );
}
