import { useMemo } from 'react';
import { Link } from '@inertiajs/react';
import { ArticleCard } from '@/components/public/articles/ArticleCard';
import { RichContent } from '@/components/public/RichContent';
import { ArticleHeader } from '@/components/public/articles/ArticleHeader';
import { ArticleTableOfContents } from '@/components/public/articles/ArticleTableOfContents';
import { AuthorBanner } from '@/components/public/articles/AuthorBanner';
import { SidebarCard } from '@/components/public/SidebarCard';
import { EmptyState } from '@/components/public/EmptyState';
import { Container } from '@/components/public/layout/Container';
import { PublicLayout } from '@/components/public/layout/PublicLayout';
import { ShareButtons } from '@/components/public/ShareButtons';
import { Button, buttonVariants } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { AUTHOR_ORGANIZATION, RELATED_ARTICLES_LIMIT } from '@/contants/public/articles';
import { useArticle, useArticles } from '@/hooks/public/useArticles';
import { prepareArticleContent } from '@/lib/article-content';
import { pickRelated } from '@/lib/related';

type ArticleShowProps = {
    slug: string;
};

function ArticleDetailSkeleton() {
    return (
        <Container className="flex flex-col gap-5 pt-14 pb-20">
            <Skeleton className="h-5 w-64" />
            <Skeleton className="h-6 w-28" />
            <Skeleton className="h-12 w-full max-w-235" />
            <Skeleton className="h-12 w-3/4 max-w-180" />
            <Skeleton className="h-6 w-full max-w-205" />
            <Skeleton className="aspect-1152/460 w-full" />
            <div className="flex max-w-190 flex-col gap-3 pt-6">
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-2/3" />
            </div>
        </Container>
    );
}

export default function ArticleShow({ slug }: ArticleShowProps) {
    const articleQuery = useArticle(slug);
    const articlesQuery = useArticles();
    const article = articleQuery.data;

    const content = useMemo(() => (article ? prepareArticleContent(article.content) : null), [article]);
    const relatedArticles = useMemo(
        () =>
            article
                ? pickRelated(
                      articlesQuery.data ?? [],
                      article,
                      (item) => item.category?.id === article.category?.id,
                      RELATED_ARTICLES_LIMIT,
                  )
                : [],
        [article, articlesQuery.data],
    );

    return (
        <PublicLayout title={article?.title ?? 'Article'}>
            {articleQuery.isPending && <ArticleDetailSkeleton />}

            {articleQuery.isError && (
                <Container className="py-20">
                    <EmptyState
                        title="Article unavailable"
                        description={articleQuery.error.message}
                        actions={
                            <>
                                <Link href="/articles" className={buttonVariants({ variant: 'outline' })}>
                                    Back to articles
                                </Link>
                                <Button onClick={() => articleQuery.refetch()} className="text-white">
                                    Try again
                                </Button>
                            </>
                        }
                    />
                </Container>
            )}

            {article && content && (
                <>
                    <Container className="pt-14 pb-10">
                        <ArticleHeader article={article} />
                    </Container>

                    <Container className="flex flex-col gap-12 pt-6 pb-20 lg:flex-row lg:items-start">
                        <article className="min-w-0 max-w-190 flex-1">
                            <RichContent html={content.html} />
                        </article>
                        <aside className="flex flex-col gap-5 lg:sticky lg:top-36 lg:w-86 lg:shrink-0">
                            <ArticleTableOfContents headings={content.headings} className="hidden lg:flex" />
                            <SidebarCard title="Share">
                                <ShareButtons title={article.title} />
                            </SidebarCard>
                        </aside>
                    </Container>

                    <AuthorBanner name={article.author?.name ?? AUTHOR_ORGANIZATION} />

                    {relatedArticles.length > 0 && (
                        <Container className="flex flex-col gap-7 pt-18 pb-22">
                            <h2 className="text-[32px] leading-10.5 font-extrabold text-foreground">Keep Reading</h2>
                            <div className="grid items-start gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {relatedArticles.map((related) => (
                                    <ArticleCard key={related.id} article={related} />
                                ))}
                            </div>
                        </Container>
                    )}
                </>
            )}
        </PublicLayout>
    );
}
