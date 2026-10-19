import type { PropsWithChildren } from 'react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

const badgeTones = {
    primary: 'bg-primary',
    secondary: 'bg-foreground',
};

const badgeSizes = {
    md: 'px-3.5 py-1.5 text-xs leading-4.5',
    sm: 'px-3 py-1 text-[11px] leading-4',
};

type CategoryBadgeProps = PropsWithChildren<{
    tone?: keyof typeof badgeTones;
    size?: keyof typeof badgeSizes;
    uppercase?: boolean;
    className?: string;
}>;

export function CategoryBadge({ tone = 'primary', size = 'md', uppercase = false, className, children }: CategoryBadgeProps) {
    return (
        <Badge
            className={cn(
                'h-auto rounded-xs font-bold text-white',
                badgeTones[tone],
                badgeSizes[size],
                uppercase && 'py-1.25 tracking-[1.2px] uppercase',
                className,
            )}
        >
            {children}
        </Badge>
    );
}
