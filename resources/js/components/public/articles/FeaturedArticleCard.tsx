import { Link } from '@inertiajs/react';
import { Skeleton } from '@/components/ui/skeleton';
import type { Article } from '@/types/public';
import { CategoryBadge } from '../CategoryBadge';
import { ArticleMeta } from './ArticleMeta';

export function FeaturedArticleCard({ article }: { article: Article }) {
    return (
        <Link
            href={`/articles/${article.slug}`}
            className="group grid overflow-hidden rounded-md border border-muted-foreground/20 bg-card outline-none transition-shadow hover:shadow-lg focus-visible:ring-3 focus-visible:ring-ring/50 md:grid-cols-2"
        >
            <div className="relative aspect-video bg-muted md:aspect-auto">
                {article.thumbnail && (
                    <img
                        src={article.thumbnail.url}
                        alt=""
                        decoding="async"
                        className="absolute inset-0 size-full object-cover"
                    />
                )}
                <span className="absolute top-6 left-6 rounded-xs bg-primary px-4 py-2 text-[13px] leading-4.5 font-bold text-white">
                    ✦ Featured
                </span>
            </div>

            <article className="flex flex-col items-start gap-3.5 p-6 sm:p-12">
                {article.category && <CategoryBadge tone="secondary">{article.category.name}</CategoryBadge>}
                <h2 className="text-2xl font-extrabold text-foreground transition-colors group-hover:text-primary sm:text-[30px] sm:leading-10.5">
                    {article.title}
                </h2>
                {article.excerpt && <p className="leading-relaxed text-muted-foreground/85">{article.excerpt}</p>}
                <ArticleMeta article={article} className="text-[13px] leading-5" />
                <span className="flex items-center gap-2 font-bold text-primary">
                    Read Article <span aria-hidden="true">→</span>
                </span>
            </article>
        </Link>
    );
}

export function FeaturedArticleCardSkeleton() {
    return (
        <div className="grid overflow-hidden rounded-md border border-muted-foreground/20 bg-card md:grid-cols-2">
            <Skeleton className="aspect-video rounded-none md:aspect-auto md:min-h-105" />
            <div className="flex flex-col gap-4 p-6 sm:p-12">
                <Skeleton className="h-7 w-28" />
                <Skeleton className="h-9 w-full" />
                <Skeleton className="h-9 w-3/4" />
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-5/6" />
                <Skeleton className="h-4 w-1/2" />
            </div>
        </div>
    );
}
