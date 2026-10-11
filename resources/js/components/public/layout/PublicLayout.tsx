import type { PropsWithChildren } from 'react';
import { Head } from '@inertiajs/react';
import { SiteFooter } from './SiteFooter';
import { SiteHeader } from './SiteHeader';

type PublicLayoutProps = PropsWithChildren<{
    title: string;
}>;

export function PublicLayout({ title, children }: PublicLayoutProps) {
    return (
        <>
            <Head title={title} />
            <div className="flex min-h-screen flex-col bg-background text-muted-foreground">
                <SiteHeader />
                <main className="flex-1">{children}</main>
                <SiteFooter />
            </div>
        </>
    );
}
