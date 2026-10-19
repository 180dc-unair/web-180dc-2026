import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { Skeleton } from '@/components/ui/skeleton';
import { estimateReadingMinutes } from '@/lib/format';
import type { Article } from '@/types/public';
import { CategoryBadge } from '../CategoryBadge';
import { ArticleMeta } from './ArticleMeta';

type FeaturedArticleCardProps = {
    article: Article;
    variant?: 'split' | 'stacked';
};

export function FeaturedArticleCard({ article, variant = 'split' }: FeaturedArticleCardProps) {
    if (variant === 'stacked') {
        const badge = [article.category?.name, `${estimateReadingMinutes(article.content)} Min Read`]
            .filter(Boolean)
            .join(' • ');

        return (
            <Link
                href={`/articles/${article.slug}`}
                className="group flex flex-col gap-5 outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
            >
                <div className="relative aspect-video overflow-hidden rounded-xl bg-muted shadow-md">
                    {article.thumbnail && (
                        <img
                            src={article.thumbnail.url}
                            alt=""
                            loading="lazy"
                            decoding="async"
                            className="size-full object-cover"
                        />
                    )}
                    <span className="absolute bottom-4 left-4 rounded-full bg-white px-4 py-1.5 text-xs font-bold text-primary">
                        {badge}
                    </span>
                    <span
                        aria-hidden="true"
                        className="absolute right-4 bottom-4 flex size-10 items-center justify-center rounded-full bg-white text-primary"
                    >
                        <ArrowRight className="size-4" />
                    </span>
                </div>
                <article className="flex flex-col gap-2">
                    <h3 className="text-2xl font-extrabold text-muted-foreground transition-colors group-hover:text-primary lg:text-3xl">
                        {article.title}
                    </h3>
                    {article.excerpt && <p className="leading-7 text-muted-foreground/85">{article.excerpt}</p>}
                </article>
            </Link>
        );
    }

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