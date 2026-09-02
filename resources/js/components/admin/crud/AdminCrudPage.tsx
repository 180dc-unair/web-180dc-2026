import { useEffect, useState } from 'react';
import { CheckCircle2, Plus, Search } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useResourceQuery } from '@/hooks/admin/useResourceQuery';
import { useDeleteResource, useToggleResource } from '@/hooks/admin/useResourceMutation';
import type { AdminRecord, ResourceConfig } from '@/types/admin';
import { ResourceTable } from './ResourceTable';
import { ResourceForm } from './ResourceForm';
import { ResourcePagination } from './ResourcePagination';
import { DeleteDialog } from './DeleteDialog';

export function AdminCrudPage({ config }: { config: ResourceConfig }) {
    const [search, setSearch] = useState('');
    const [formOpen, setFormOpen] = useState(false);
    const [selected, setSelected] = useState<AdminRecord | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<AdminRecord | null>(null);
    const [feedback, setFeedback] = useState('');
    const [page, setPage] = useState(1);

    const { rows, meta, isLoading, isError, isFetching, isSuccess, refetch } =
        useResourceQuery(config, search, page);

    const deleteMutation = useDeleteResource(config);
    const toggleMutation = useToggleResource(config);

    useEffect(() => {
        if (meta && meta.current_page !== page) {
            setPage(meta.current_page);
        }
    }, [meta, page]);

    const handleSearch = (value: string) => {
        setSearch(value);
        setPage(1);
    };

    const handleDelete = (record: AdminRecord) => {
        deleteMutation.mutate(record, {
            onSuccess: () => {
                setFeedback(`${config.singular} berhasil dihapus.`);
                setDeleteTarget(null);
            },
        });
    };

    const handleToggle = (record: AdminRecord, field: string) => {
        toggleMutation.mutate({ record, field });
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-black sm:text-3xl">{config.title}</h1>
                    <p className="mt-1 text-sm text-black/60">{config.description}</p>
                </div>
                <Button onClick={() => { setSelected(null); setFormOpen(true); }} size="lg">
                    <Plus /> Tambah {config.singular}
                </Button>
            </div>

            {feedback && (
                <Alert>
                    <CheckCircle2 />
                    <AlertTitle>Berhasil</AlertTitle>
                    <AlertDescription>{feedback}</AlertDescription>
                </Alert>
            )}

            <Card>
                <CardHeader className="gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <CardTitle className="text-base text-black">Daftar {config.title}</CardTitle>
                    <div className="relative w-full sm:max-w-xs">
                        <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-black/45" />
                        <Input
                            value={search}
                            onChange={(event) => handleSearch(event.target.value)}
                            placeholder="Cari data..."
                            className="pl-9"
                        />
                    </div>
                </CardHeader>
                <CardContent className="p-0">
                    <ResourceTable
                        config={config}
                        rows={rows}
                        isLoading={isLoading}
                        isError={isError}
                        isSuccess={isSuccess}
                        isTogglePending={toggleMutation.isPending}
                        onRefetch={refetch}
                        onEdit={(record) => { setSelected(record); setFormOpen(true); }}
                        onDelete={setDeleteTarget}
                        onToggle={handleToggle}
                    />
                    {meta && (
                        <ResourcePagination
                            meta={meta}
                            isFetching={isFetching}
                            onPrev={() => setPage((p) => Math.max(1, p - 1))}
                            onNext={() => setPage((p) => p + 1)}
                        />
                    )}
                </CardContent>
            </Card>

            <ResourceForm
                config={config}
                record={selected}
                open={formOpen}
                onOpenChange={setFormOpen}
                onSaved={setFeedback}
            />

            <DeleteDialog
                config={config}
                target={deleteTarget}
                isPending={deleteMutation.isPending}
                isError={deleteMutation.isError}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                onConfirm={handleDelete}
            />
        </div>
    );
}
