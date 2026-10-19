import { useMemo } from 'react';
import { useActiveHeading } from '@/hooks/public/useActiveHeading';
import type { ArticleHeading } from '@/lib/article-content';
import { cn } from '@/lib/utils';
import { SidebarCard } from '../SidebarCard';

export function ArticleTableOfContents({ headings, className }: { headings: ArticleHeading[]; className?: string }) {
    const headingIds = useMemo(() => headings.map((heading) => heading.id), [headings]);
    const activeId = useActiveHeading(headingIds);

    if (headings.length === 0) {
        return null;
    }

    return (
        <SidebarCard title="On this page" className={className}>
            <nav aria-label="On this page">
                <ul className="flex flex-col gap-3">
                    {headings.map((heading) => (
                        <li key={heading.id}>
                            <a
                                href={`#${heading.id}`}
                                aria-current={heading.id === activeId ? 'location' : undefined}
                                className={cn(
                                    'block text-sm leading-6 transition-colors hover:text-primary',
                                    heading.id === activeId ? 'font-semibold text-primary' : 'text-muted-foreground',
                                )}
                            >
                                {heading.text}
                            </a>
                        </li>
                    ))}
                </ul>
            </nav>
        </SidebarCard>
    );
}
