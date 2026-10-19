import { Fragment } from 'react';
import { Link } from '@inertiajs/react';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbEntry } from '@/types/public';

export function PageBreadcrumb({ items }: { items: BreadcrumbEntry[] }) {
    return (
        <Breadcrumb>
            <BreadcrumbList className="gap-2 text-[13px] leading-5 font-medium text-muted-foreground/85">
                {items.map((item, index) => (
                    <Fragment key={item.label}>
                        {index > 0 && <BreadcrumbSeparator>/</BreadcrumbSeparator>}
                        <BreadcrumbItem>
                            {item.href ? (
                                <BreadcrumbLink render={<Link href={item.href} />} className="hover:text-primary">
                                    {item.label}
                                </BreadcrumbLink>
                            ) : (
                                <BreadcrumbPage className="max-w-72 truncate font-semibold text-primary">{item.label}</BreadcrumbPage>
                            )}
                        </BreadcrumbItem>
                    </Fragment>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
