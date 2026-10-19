import { Head } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Eye, ReceiptText, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AdminLayout } from '@/components/admin/AdminLayout';
import { OrderDetailDialog } from '@/components/admin/OrderDetailDialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useOrdersQuery, useUpdateOrderStatus, type AdminOrder } from '@/hooks/admin/useOrders';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { ApiError } from '@/lib/admin-api';

const STATUS_OPTIONS: Array<{ value: string; label: string }> = [
    { value: 'all', label: 'Semua status' },
    { value: 'pending', label: 'Menunggu' },
    { value: 'paid', label: 'Dibayar' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
    { value: 'expired', label: 'Kedaluwarsa' },
];

const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu',
    paid: 'Dibayar',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    expired: 'Kedaluwarsa',
};

const CURRENCY_FORMATTER = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

function errorMessage(error: unknown): string {
    if (error instanceof ApiError) return error.message;
    if (error instanceof Error) return error.message;
    return 'Operasi tidak dapat diproses.';
}

export default function AdminOrders() {
    const [searchInput, setSearchInput] = useState('');
    const [search] = useDebouncedValue(searchInput, 300);
    const [status, setStatus] = useState('all');
    const [page, setPage] = useState(1);
    const [statusTarget, setStatusTarget] = useState<{ order: AdminOrder; next: 'completed' | 'cancelled' } | null>(null);
    const [detailId, setDetailId] = useState<string | null>(null);
    const [feedback, setFeedback] = useState('');

    const params = useMemo(() => {
        const next: { search?: string; status?: string; per_page: number; page: number } = { per_page: 15, page };
        const trimmed = search.trim();
        if (trimmed) next.search = trimmed;
        if (status !== 'all') next.status = status;
        return next;
    }, [search, status, page]);

    const orders = useOrdersQuery(params);
    const updateStatus = useUpdateOrderStatus();

    const handleConfirm = () => {
        if (!statusTarget) return;
        updateStatus.mutate(
            { id: statusTarget.order.id, status: statusTarget.next },
            {
                onSuccess: () => {
                    setFeedback(
                        statusTarget.next === 'completed'
                            ? `Pesanan ${statusTarget.order.order_number} ditandai selesai.`
                            : `Pesanan ${statusTarget.order.order_number} dibatalkan.`,
                    );
                    setStatusTarget(null);
                },
            },
        );
    };

    const error = errorMessage(orders.error ?? updateStatus.error);

    return (
        <AdminLayout title="Pesanan" description="Kelola pesanan masuk dari publik">
            <Head title="Pesanan - Admin" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-black sm:text-3xl">Daftar Pesanan</h1>
                    <p className="mt-1 text-sm text-black/60">Pantau pesanan masuk, tandai selesai, atau batalkan pesanan.</p>
                </div>

                {(orders.isError || updateStatus.isError) && (
                    <Alert variant="destructive">
                        <AlertCircle />
                        <AlertTitle>Operasi gagal</AlertTitle>
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {feedback && (
                    <Alert>
                        <CheckCircle2 />
                        <AlertTitle>Berhasil</AlertTitle>
                        <AlertDescription>{feedback}</AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardHeader className="gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <CardTitle className="flex items-center gap-2 text-base text-black">
                            <ReceiptText className="size-4" /> Pesanan
                        </CardTitle>
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <div className="relative sm:w-72">
                                <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-black/45" />
                                <Input
                                    value={searchInput}
                                    onChange={(event) => {
                                        setSearchInput(event.target.value);
                                        setPage(1);
                                    }}
                                    placeholder="Cari nomor / email..."
                                    className="pl-9"
                                />
                            </div>
                            <Select value={status} onValueChange={(value) => { setStatus(String(value)); setPage(1); }}>
                                <SelectTrigger className="w-full sm:w-44">
                                    <SelectValue>{STATUS_OPTIONS.find((o) => o.value === status)?.label ?? 'Semua status'}</SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    {STATUS_OPTIONS.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {orders.isLoading ? (
                            <div className="space-y-3 p-6">
                                {[1, 2, 3, 4, 5].map((item) => (
                                    <Skeleton key={item} className="h-14 w-full" />
                                ))}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Nomor Pesanan</TableHead>
                                            <TableHead>Pelanggan</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Item</TableHead>
                                            <TableHead>Total</TableHead>
                                            <TableHead>Tanggal</TableHead>
                                            <TableHead className="text-right">Aksi</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {orders.data?.items.map((order) => (
                                            <TableRow key={order.id}>
                                                <TableCell className="font-mono text-xs text-black">{order.order_number}</TableCell>
                                                <TableCell>
                                                    <p className="font-medium text-black">{order.customer_name}</p>
                                                    <p className="text-xs text-black/55">{order.customer_email}</p>
                                                </TableCell>
                                                <TableCell className="text-black">{STATUS_LABEL[order.status] ?? order.status}</TableCell>
                                                <TableCell className="text-black/70">{order.item_count ?? '-'}</TableCell>
                                                <TableCell className="text-black">{CURRENCY_FORMATTER.format(Number(order.total_amount ?? 0))}</TableCell>
                                                <TableCell className="whitespace-nowrap text-black/55">
                                                    {new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(order.created_at))}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex justify-end gap-1">
                                                        <Button
                                                            variant="ghost"
                                                            size="icon-sm"
                                                            onClick={() => setDetailId(order.id)}
                                                            aria-label="Lihat detail pesanan"
                                                        >
                                                            <Eye />
                                                        </Button>
                                                        {order.status === 'paid' && (
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => setStatusTarget({ order, next: 'completed' })}
                                                                disabled={updateStatus.isPending}
                                                            >
                                                                Tandai Selesai
                                                            </Button>
                                                        )}
                                                        {order.status === 'pending' && (
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                className="text-destructive"
                                                                onClick={() => setStatusTarget({ order, next: 'cancelled' })}
                                                                disabled={updateStatus.isPending}
                                                            >
                                                                Batalkan
                                                            </Button>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                        {!orders.data?.items.length && (
                                            <TableRow>
                                                <TableCell colSpan={7} className="h-32 text-center text-black/55">
                                                    Belum ada pesanan yang sesuai.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {orders.data && orders.data.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-black/60">
                        <p>
                            Halaman {orders.data.meta.current_page} dari {orders.data.meta.last_page} ({orders.data.meta.total} pesanan)
                        </p>
                        <div className="flex gap-2">
                            <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage((value) => Math.max(1, value - 1))}>
                                Sebelumnya
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={page >= orders.data.meta.last_page}
                                onClick={() => setPage((value) => Math.min(orders.data?.meta.last_page ?? value, value + 1))}
                            >
                                Berikutnya
                            </Button>
                        </div>
                    </div>
                )}
            </div>

            <AlertDialog open={Boolean(statusTarget)} onOpenChange={(open) => !open && setStatusTarget(null)}>
                <AlertDialogContent className="text-black [&_[data-slot=alert-dialog-description]]:text-black/60 [&_[data-slot=alert-dialog-title]]:text-black">
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {statusTarget?.next === 'completed' ? 'Tandai pesanan selesai?' : 'Batalkan pesanan?'}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {statusTarget?.next === 'completed'
                                ? `Pesanan ${statusTarget.order.order_number} akan ditandai sebagai selesai.`
                                : statusTarget
                                    ? `Pesanan ${statusTarget.order.order_number} akan dibatalkan dan stok akan dikembalikan.`
                                    : ''}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction
                            variant={statusTarget?.next === 'cancelled' ? 'destructive' : 'default'}
                            disabled={updateStatus.isPending}
                            onClick={handleConfirm}
                        >
                            {updateStatus.isPending ? 'Memproses...' : 'Konfirmasi'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <OrderDetailDialog
                orderId={detailId}
                open={Boolean(detailId)}
                onOpenChange={(open) => !open && setDetailId(null)}
            />
        </AdminLayout>
    );
}
