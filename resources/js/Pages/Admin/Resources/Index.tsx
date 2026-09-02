import { Head } from '@inertiajs/react';
import { AdminCrudPage } from '@/components/admin/AdminCrudPage';
import { AdminLayout } from '@/components/admin/AdminLayout';
import { getResourceConfig } from '@/features/admin/resource-config';

type ResourcePageProps = {
    resourceKey: string;
    pageTitle: string;
};

export default function AdminResourcePage({ resourceKey, pageTitle }: ResourcePageProps) {
    const config = getResourceConfig(resourceKey);

    if (!config) {
        return (
            <AdminLayout title={pageTitle}>
                <Head title={pageTitle} />
                <p className="text-black/60">Resource tidak ditemukan.</p>
            </AdminLayout>
        );
    }

    return (
        <AdminLayout title={config.title} description={config.description}>
            <Head title={`${config.title} - Admin`} />
            <AdminCrudPage config={config} />
        </AdminLayout>
    );
}
