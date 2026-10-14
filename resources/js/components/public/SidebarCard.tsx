import type { PropsWithChildren } from 'react';
import { cn } from '@/lib/utils';

type SidebarCardProps = PropsWithChildren<{
    title: string;
    className?: string;
}>;

export function SidebarCard({ title, className, children }: SidebarCardProps) {
    return (
        <section className={cn('flex flex-col gap-3 rounded-md border border-muted-foreground/20 bg-card p-6', className)}>
            <h2 className="text-xs leading-4 font-bold tracking-[1.4px] text-muted-foreground/85 uppercase">{title}</h2>
            {children}
        </section>
    );
}
