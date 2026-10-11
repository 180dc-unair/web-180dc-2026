import type { PropsWithChildren } from 'react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type CategoryBadgeProps = PropsWithChildren<{
    tone?: 'primary' | 'secondary';
    className?: string;
}>;

export function CategoryBadge({ tone = 'primary', className, children }: CategoryBadgeProps) {
    return (
        <Badge
            className={cn(
                'h-auto rounded-xs px-3.5 py-1.5 text-xs leading-4.5 font-bold text-white',
                tone === 'primary' ? 'bg-primary' : 'bg-foreground',
                className,
            )}
        >
            {children}
        </Badge>
    );
}
