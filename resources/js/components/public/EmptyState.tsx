import type { ReactNode } from 'react';

type EmptyStateProps = {
    title: string;
    description: string;
    actions?: ReactNode;
};

export function EmptyState({ title, description, actions }: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center gap-3 rounded-md border border-dashed border-border px-6 py-16 text-center">
            <p className="text-lg font-semibold text-muted-foreground">{title}</p>
            <p className="max-w-md text-sm text-muted-foreground/85">{description}</p>
            {actions && <div className="mt-2 flex flex-wrap justify-center gap-3">{actions}</div>}
        </div>
    );
}
