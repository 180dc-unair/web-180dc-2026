import { Head } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Eye, Search, Wallet } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AdminLayout } from '@/components/admin/AdminLayout';
import { PaymentDetailDialog } from '@/components/admin/PaymentDetailDialog';
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
import { useConfirmPayment, usePaymentsQuery, type AdminPayment } from '@/hooks/admin/usePayments';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { ApiError } from '@/lib/admin-api';

const STATUS_OPTIONS: Array<{ value: string; label: string }> = [
    { value: 'all', label: 'Semua status' },
    { value: 'pending', label: 'Menunggu' },
    { value: 'paid', label: 'Dibayar' },
    { value: 'failed', label: 'Gagal' },
    { value: 'expired', label: 'Kedaluwarsa' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

const GATEWAY_OPTIONS: Array<{ value: string; label: string }> = [
    { value: 'all', label: 'Semua gateway' },
    { value: 'manual', label: 'Manual' },
    { value: 'midtrans', label: 'Midtrans' },
];

const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu',
    paid: 'Dibayar',
    failed: 'Gagal',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
};

const GATEWAY_LABEL: Record<string, string> = {
    manual: 'Manual',
    midtrans: 'Midtrans',
};

const CURRENCY_FORMATTER = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

function errorMessage(error: unknown): string {
    if (error instanceof ApiError) return error.message;
    if (error instanceof Error) return error.message;
    return 'Operasi tidak dapat diproses.';
}

export default function AdminPayments() {
    const [searchInput, setSearchInput] = useState('');
    const [search] = useDebouncedValue(searchInput, 300);
    const [status, setStatus] = useState('all');
    const [gateway, setGateway] = useState('all');
    const [page, setPage] = useState(1);
    const [confirmTarget, setConfirmTarget] = useState<AdminPayment | null>(null);
    const [detailId, setDetailId] = useState<string | null>(null);
    const [feedback, setFeedback] = useState('');

    const params = useMemo(() => {
        const next: { status?: string; gateway?: string; order_number?: string; per_page: number; page: number } = { per_page: 15, page };
        const trimmed = search.trim();
        if (trimmed) next.order_number = trimmed;
        if (status !== 'all') next.status = status;
        if (gateway !== 'all') next.gateway = gateway;
        return next;
    }, [search, status, gateway, page]);

    const payments = usePaymentsQuery(params);
    const confirm = useConfirmPayment();

    const handleConfirm = () => {
        if (!confirmTarget) return;
        confirm.mutate(confirmTarget.id, {
            onSuccess: () => {
                setFeedback(`Pembayaran ${confirmTarget.id.slice(0, 8)} berhasil dikonfirmasi.`);
                setConfirmTarget(null);
            },
        });
    };

    const error = errorMessage(payments.error ?? confirm.error);

    return (
        <AdminLayout title="Pembayaran" description="Kelola transaksi pembayaran masuk">
            <Head title="Pembayaran - Admin" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-black sm:text-3xl">Daftar Pembayaran</h1>
                    <p className="mt-1 text-sm text-black/60">Pantau dan konfirmasi pembayaran manual dari pelanggan.</p>
                </div>

                {(payments.isError || confirm.isError) && (
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
                            <Wallet className="size-4" /> Pembayaran
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
                                    placeholder="Cari nomor pesanan..."
                                    className="pl-9"
                                />
                            </div>
                            <Select value={status} onValueChange={(value) => { setStatus(String(value)); setPage(1); }}>
                                <SelectTrigger className="w-full sm:w-40">
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
                            <Select value={gateway} onValueChange={(value) => { setGateway(String(value)); setPage(1); }}>
                                <SelectTrigger className="w-full sm:w-40">
                                    <SelectValue>{GATEWAY_OPTIONS.find((o) => o.value === gateway)?.label ?? 'Semua gateway'}</SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    {GATEWAY_OPTIONS.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {payments.isLoading ? (
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
                                            <TableHead>ID Pembayaran</TableHead>
                                            <TableHead>Metode</TableHead>
                                            <TableHead>Gateway</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Jumlah</TableHead>
                                            <TableHead>Tanggal</TableHead>
                                            <TableHead className="text-right">Aksi</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {payments.data?.items.map((payment) => (
                                            <TableRow key={payment.id}>
                                                <TableCell className="font-mono text-xs text-black">{payment.id.slice(0, 8)}</TableCell>
                                                <TableCell className="text-black">{payment.payment_method?.name ?? '-'}</TableCell>
                                                <TableCell className="text-black">{GATEWAY_LABEL[payment.gateway] ?? payment.gateway}</TableCell>
                                                <TableCell className="text-black">{STATUS_LABEL[payment.status] ?? payment.status}</TableCell>
                                                <TableCell className="text-black">{CURRENCY_FORMATTER.format(Number(payment.amount ?? 0))}</TableCell>
                                                <TableCell className="whitespace-nowrap text-black/55">
                                                    {new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(payment.created_at))}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex justify-end gap-1">
                                                        <Button
                                                            variant="ghost"
                                                            size="icon-sm"
                                                            onClick={() => setDetailId(payment.id)}
                                                            aria-label="Lihat detail pembayaran"
                                                        >
                                                            <Eye />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            disabled={payment.status !== 'pending' || payment.gateway !== 'manual' || confirm.isPending}
                                                            onClick={() => setConfirmTarget(payment)}
                                                        >
                                                            Konfirmasi
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                        {!payments.data?.items.length && (
                                            <TableRow>
                                                <TableCell colSpan={7} className="h-32 text-center text-black/55">
                                                    Belum ada pembayaran yang sesuai.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {payments.data && payments.data.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-black/60">
                        <p>
                            Halaman {payments.data.meta.current_page} dari {payments.data.meta.last_page} ({payments.data.meta.total} pembayaran)
                        </p>
                        <div className="flex gap-2">
                            <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage((value) => Math.max(1, value - 1))}>
                                Sebelumnya
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={page >= payments.data.meta.last_page}
                                onClick={() => setPage((value) => Math.min(payments.data?.meta.last_page ?? value, value + 1))}
                            >
                                Berikutnya
                            </Button>
                        </div>
                    </div>
                )}
            </div>

            <AlertDialog open={Boolean(confirmTarget)} onOpenChange={(open) => !open && setConfirmTarget(null)}>
                <AlertDialogContent className="text-black [&_[data-slot=alert-dialog-description]]:text-black/60 [&_[data-slot=alert-dialog-title]]:text-black">
                    <AlertDialogHeader>
                        <AlertDialogTitle>Konfirmasi pembayaran manual?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Pembayaran akan ditandai lunas, pesanan akan berpindah ke status dibayar, dan stok fisik akan dikurangi.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction disabled={confirm.isPending} onClick={handleConfirm}>
                            {confirm.isPending ? 'Memproses...' : 'Konfirmasi'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <PaymentDetailDialog
                paymentId={detailId}
                open={Boolean(detailId)}
                onOpenChange={(open) => !open && setDetailId(null)}
            />
        </AdminLayout>
    );
}
