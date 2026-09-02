import { AlertCircle, Pencil, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { AdminRecord, ResourceConfig } from '@/types/admin';
import { CellValue } from './CellValue';
import { ToggleButton } from './ToggleButton';
import { getPath } from './resource-utils';

type ResourceTableProps = {
    config: ResourceConfig;
    rows: AdminRecord[];
    isLoading: boolean;
    isError: boolean;
    isSuccess: boolean;
    isTogglePending: boolean;
    onRefetch: () => void;
    onEdit: (record: AdminRecord) => void;
    onDelete: (record: AdminRecord) => void;
    onToggle: (record: AdminRecord, field: string) => void;
};

export function ResourceTable({
    config,
    rows,
    isLoading,
    isError,
    isSuccess,
    isTogglePending,
    onRefetch,
    onEdit,
    onDelete,
    onToggle,
}: ResourceTableProps) {
    if (isLoading) {
        return (
            <div className="space-y-3 p-6">
                {[1, 2, 3, 4].map((item) => <Skeleton key={item} className="h-12 w-full" />)}
            </div>
        );
    }

    if (isError) {
        return (
            <div className="p-6">
                <Alert variant="destructive">
                    <AlertCircle />
                    <AlertTitle>Data gagal dimuat</AlertTitle>
                    <AlertDescription className="flex items-center justify-between gap-3">
                        Periksa koneksi lalu coba kembali.
                        <Button size="sm" variant="outline" onClick={onRefetch}>Coba lagi</Button>
                    </AlertDescription>
                </Alert>
            </div>
        );
    }

    if (!isSuccess) return null;

    return (
        <div className="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        {config.columns.map((column) => (
                            <TableHead key={column.key} className="text-black">{column.label}</TableHead>
                        ))}
                        <TableHead className="w-32 text-right text-black">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {rows.map((record) => (
                        <TableRow key={record.id}>
                            {config.columns.map((column) => (
                                <TableCell key={column.key}>
                                    <CellValue value={getPath(record, column.key)} type={column.type} />
                                </TableCell>
                            ))}
                            <TableCell>
                                <div className="flex justify-end gap-1">
                                    {config.key === 'products' && (
                                        <>
                                            <ToggleButton
                                                record={record}
                                                field="is_featured"
                                                icon="star"
                                                disabled={isTogglePending}
                                                onToggle={onToggle}
                                            />
                                            <ToggleButton
                                                record={record}
                                                field="is_best_seller"
                                                icon="flame"
                                                disabled={isTogglePending}
                                                onToggle={onToggle}
                                            />
                                        </>
                                    )}
                                    <Button variant="ghost" size="icon-sm" onClick={() => onEdit(record)} aria-label={`Edit ${config.singular}`}>
                                        <Pencil />
                                    </Button>
                                    <Button variant="ghost" size="icon-sm" onClick={() => onDelete(record)} aria-label={`Hapus ${config.singular}`} className="text-destructive">
                                        <Trash2 />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                    {rows.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={config.columns.length + 1} className="h-32 text-center text-black/55">
                                Belum ada data yang sesuai.
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
