import { Link } from '@inertiajs/react';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import type { Article } from '@/types/public';
import { CategoryBadge } from '../CategoryBadge';
import { ArticleMeta } from './ArticleMeta';

const cardClassName = 'flex flex-col overflow-hidden rounded-md border border-muted-foreground/20 bg-card';

export function ArticleCard({ article }: { article: Article }) {
    return (
        <Link
            href={`/articles/${article.slug}`}
            className={cn(
                cardClassName,
                'group outline-none transition-shadow hover:shadow-lg focus-visible:ring-3 focus-visible:ring-ring/50',
            )}
        >
            <article>
                <div className="relative h-50 bg-muted">
                    {article.thumbnail && (
                        <img src={article.thumbnail.url} alt="" loading="lazy" className="size-full object-cover" />
                    )}
                    {article.category && (
                        <CategoryBadge className="absolute top-4 left-4">{article.category.name}</CategoryBadge>
                    )}
                </div>
                <div className="flex flex-col gap-2.5 p-6">
                    <h3 className="text-lg leading-6.5 font-semibold text-foreground transition-colors group-hover:text-primary">
                        {article.title}
                    </h3>
                    {article.excerpt && <p className="text-sm leading-5 text-muted-foreground/85">{article.excerpt}</p>}
                    <ArticleMeta article={article} className="text-xs leading-4.5" />
                </div>
            </article>
        </Link>
    );
}

export function ArticleCardSkeleton() {
    return (
        <div className={cardClassName}>
            <Skeleton className="h-50 rounded-none" />
            <div className="flex flex-col gap-3 p-6">
                <Skeleton className="h-5 w-4/5" />
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-2/3" />
                <Skeleton className="h-3 w-1/2" />
            </div>
        </div>
    );
}
