import { estimateReadingMinutes, formatDate, formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Article } from '@/types/public';

export function ArticleMeta({ article, className }: { article: Article; className?: string }) {
    const publishedAt = article.published_at ?? article.created_at;

    return (
        <p className={cn('flex flex-wrap items-center gap-2 text-muted-foreground/85', className)}>
            <time dateTime={publishedAt}>{formatDate(publishedAt)}</time>
            <span aria-hidden="true">•</span>
            <span>{estimateReadingMinutes(article.content)} min read</span>
            <span aria-hidden="true">•</span>
            <span>{formatNumber(article.view_count)} views</span>
        </p>
    );
}
