import { useMemo, useRef } from 'react';
import garudaAmerta from '@/assets/hero/garuda-amerta.png';
import rhombusCluster from '@/assets/hero/rhombus-cluster-articles.svg';
import { ArticleCard, ArticleCardSkeleton } from '@/components/public/articles/ArticleCard';
import { FeaturedArticleCard, FeaturedArticleCardSkeleton } from '@/components/public/articles/FeaturedArticleCard';
import { FilterChips } from '@/components/public/FilterChips';
import { Container } from '@/components/public/layout/Container';
import { PublicLayout } from '@/components/public/layout/PublicLayout';
import { PageHero } from '@/components/public/PageHero';
import { PaginationNav } from '@/components/public/PaginationNav';
import { SortSelect } from '@/components/public/SortSelect';
import { Button } from '@/components/ui/button';
import {
    ALL_CATEGORIES_OPTION,
    ARTICLE_SORT_OPTIONS,
    ARTICLES_HERO,
    ARTICLES_PER_PAGE,
} from '@/contants/public/articles';
import { useArticleCategories, useArticles } from '@/hooks/public/useArticles';
import { useQueryParams } from '@/hooks/public/useQueryParams';
import { paginate } from '@/lib/pagination';
import type { Article } from '@/types/public';

const DEFAULT_FILTERS = { category: ALL_CATEGORIES_OPTION.value, sort: 'newest', page: '1' };

const publishedTime = (article: Article) => new Date(article.published_at ?? article.created_at).getTime();

const articleComparators: Record<string, (a: Article, b: Article) => number> = {
    newest: (a, b) => publishedTime(b) - publishedTime(a),
    oldest: (a, b) => publishedTime(a) - publishedTime(b),
    popular: (a, b) => b.view_count - a.view_count,
};

function sortArticles(articles: Article[], sort: string): Article[] {
    const comparator = articleComparators[sort] ?? articleComparators.newest;

    return [...articles].sort(comparator);
}

function ArticleListSkeleton() {
    return (
        <>
            <FeaturedArticleCardSkeleton />
            <div className="grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                {Array.from({ length: ARTICLES_PER_PAGE }, (_, index) => (
                    <ArticleCardSkeleton key={index} />
                ))}
            </div>
        </>
    );
}

function ArticleListMessage({ title, description, onRetry }: { title: string; description: string; onRetry?: () => void }) {
    return (
        <div className="flex flex-col items-center gap-3 rounded-md border border-dashed border-border px-6 py-16 text-center">
            <p className="text-lg font-semibold text-muted-foreground">{title}</p>
            <p className="max-w-md text-sm text-muted-foreground/85">{description}</p>
            {onRetry && (
                <Button variant="outline" onClick={onRetry} className="mt-2">
                    Try again
                </Button>
            )}
        </div>
    );
}

export default function ArticlesIndex() {
    const listRef = useRef<HTMLDivElement>(null);
    const [filters, setFilters] = useQueryParams(DEFAULT_FILTERS);
    const category = filters.category === ALL_CATEGORIES_OPTION.value ? undefined : filters.category;

    const articlesQuery = useArticles({ category });
    const categoriesQuery = useArticleCategories();

    const categoryOptions = useMemo(
        () => [
            ALL_CATEGORIES_OPTION,
            ...(categoriesQuery.data ?? []).map((item) => ({ label: item.name, value: item.slug })),
        ],
        [categoriesQuery.data],
    );

    const [featured, ...others] = useMemo(
        () => sortArticles(articlesQuery.data ?? [], filters.sort),
        [articlesQuery.data, filters.sort],
    );

    const totalPages = Math.max(1, Math.ceil(others.length / ARTICLES_PER_PAGE));
    const currentPage = Math.min(Math.max(Number(filters.page) || 1, 1), totalPages);
    const visibleArticles = paginate(others, currentPage, ARTICLES_PER_PAGE);

    const changePage = (page: number) => {
        setFilters({ page: String(page) });
        listRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    return (
        <PublicLayout title="Articles">
            <PageHero
                {...ARTICLES_HERO}
                decoration={rhombusCluster}
                illustration={garudaAmerta}
                illustrationClassName="top-[calc(50%-31px)] h-125.5 w-105 -translate-y-1/2"
            />

            <Container className="flex flex-col gap-4 py-8 md:flex-row md:items-start md:justify-between">
                <FilterChips
                    label="Article categories"
                    options={categoryOptions}
                    value={filters.category}
                    onChange={(value) => setFilters({ category: value, page: DEFAULT_FILTERS.page })}
                />
                <SortSelect
                    options={ARTICLE_SORT_OPTIONS}
                    value={filters.sort}
                    onChange={(value) => setFilters({ sort: value, page: DEFAULT_FILTERS.page })}
                />
            </Container>

            <Container ref={listRef} className="flex scroll-mt-36 flex-col gap-14 pb-22">
                {articlesQuery.isPending && <ArticleListSkeleton />}

                {articlesQuery.isError && (
                    <ArticleListMessage
                        title="Articles could not be loaded"
                        description="Something went wrong while fetching articles. Please try again."
                        onRetry={() => articlesQuery.refetch()}
                    />
                )}

                {articlesQuery.isSuccess && !featured && (
                    <ArticleListMessage
                        title="No articles yet"
                        description="There are no published articles in this category. Check back soon."
                    />
                )}

                {articlesQuery.isSuccess && featured && (
                    <>
                        {currentPage === 1 && <FeaturedArticleCard article={featured} />}

                        {visibleArticles.length > 0 && (
                            <div className="grid items-start gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                                {visibleArticles.map((article) => (
                                    <ArticleCard key={article.id} article={article} />
                                ))}
                            </div>
                        )}

                        <PaginationNav currentPage={currentPage} totalPages={totalPages} onPageChange={changePage} />
                    </>
                )}
            </Container>
        </PublicLayout>
    );
}
