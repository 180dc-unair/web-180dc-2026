import { ARTICLE_HERO_SHARDS, AUTHOR_ORGANIZATION } from '@/contants/public/articles';
import type { Article } from '@/types/public';
import { CategoryBadge } from '../CategoryBadge';
import { PageBreadcrumb } from '../PageBreadcrumb';
import { ShardCluster } from '../ShardCluster';
import { ArticleMeta } from './ArticleMeta';
import { AuthorAvatar } from './AuthorAvatar';

export function ArticleHeader({ article }: { article: Article }) {
    const authorName = article.author?.name ?? AUTHOR_ORGANIZATION;

    return (
        <header className="flex flex-col items-start gap-5">
            <PageBreadcrumb
                items={[{ label: 'Home', href: '/' }, { label: 'Articles', href: '/articles' }, { label: article.title }]}
            />

            {article.category && (
                <CategoryBadge tone="secondary" className="px-3 py-1.25 text-[11px] leading-4 tracking-[1.2px] uppercase">
                    {article.category.name}
                </CategoryBadge>
            )}

            <h1 className="max-w-235 text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl lg:text-[46px] lg:leading-14.5">
                {article.title}
            </h1>

            {article.excerpt && (
                <p className="max-w-205 text-base leading-7.5 font-medium text-muted-foreground sm:text-lg">{article.excerpt}</p>
            )}

            <div className="flex flex-wrap items-center gap-3 py-2">
                <div className="flex items-center gap-3">
                    <AuthorAvatar name={authorName} />
                    <div className="flex flex-col gap-0.5">
                        <span className="text-sm leading-5 font-semibold text-muted-foreground">{authorName}</span>
                        <span className="text-xs leading-4.5 text-muted-foreground/85">{AUTHOR_ORGANIZATION}</span>
                    </div>
                </div>
                <span aria-hidden="true" className="hidden text-[13px] text-muted-foreground/50 sm:inline">
                    •
                </span>
                <ArticleMeta article={article} className="gap-3 text-[13px] leading-5 font-medium" />
            </div>

            {article.thumbnail && (
                <div className="relative aspect-1152/460 w-full overflow-hidden rounded-md bg-muted">
                    <img src={article.thumbnail.url} alt="" fetchPriority="high" className="size-full object-cover" />
                    <ShardCluster shards={ARTICLE_HERO_SHARDS} className="hidden lg:block" />
                </div>
            )}
        </header>
    );
}
