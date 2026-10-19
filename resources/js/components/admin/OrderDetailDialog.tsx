import { AlertCircle, ExternalLink, Package } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { AdminOrder, AdminOrderItem, AdminOrderPaymentRef } from '@/services/admin/orders.service';
import { useOrderQuery } from '@/hooks/admin/useOrders';
import { ApiError } from '@/lib/admin-api';

const CURRENCY_FORMATTER = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
const DATE_FORMATTER = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' });

const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu',
    paid: 'Dibayar',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    expired: 'Kedaluwarsa',
};

const PAYMENT_LABEL: Record<string, string> = {
    pending: 'Menunggu',
    paid: 'Dibayar',
    failed: 'Gagal',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
};

function StatusBadge({ status }: { status: string }) {
    return <Badge variant="outline" className="text-black">{STATUS_LABEL[status] ?? status}</Badge>;
}

function PaymentStatusBadge({ status }: { status: string }) {
    return <Badge variant="outline" className="text-black">{PAYMENT_LABEL[status] ?? status}</Badge>;
}

export function OrderDetailDialog({
    orderId,
    open,
    onOpenChange,
}: {
    orderId: string | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const order = useOrderQuery(open ? orderId : null);

    const data: AdminOrder | undefined = order.data;

    const error = order.error instanceof ApiError ? order.error.message : order.error instanceof Error ? order.error.message : null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[92vh] overflow-y-auto text-black sm:max-w-3xl [&_[data-slot=dialog-description]]:text-black/60 [&_[data-slot=dialog-title]]:text-black">
                <DialogHeader>
                    <DialogTitle>Detail Pesanan</DialogTitle>
                    <DialogDescription>Ringkasan pesanan, item, dan status pembayaran.</DialogDescription>
                </DialogHeader>

                {order.isLoading && (
                    <div className="space-y-3">
                        <Skeleton className="h-6 w-1/2" />
                        <Skeleton className="h-24 w-full" />
                        <Skeleton className="h-32 w-full" />
                    </div>
                )}

                {error && (
                    <Alert variant="destructive">
                        <AlertCircle />
                        <AlertTitle>Detail gagal dimuat</AlertTitle>
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {data && (
                    <div className="space-y-6">
                        <section className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p className="text-xs uppercase tracking-wide text-black/55">Nomor Pesanan</p>
                                <p className="font-mono text-sm font-semibold text-black">{data.order_number}</p>
                            </div>
                            <div>
                                <p className="text-xs uppercase tracking-wide text-black/55">Status</p>
                                <div className="mt-1"><StatusBadge status={data.status} /></div>
                            </div>
                            <div>
                                <p className="text-xs uppercase tracking-wide text-black/55">Pelanggan</p>
                                <p className="font-medium text-black">{data.customer_name}</p>
                                <p className="text-xs text-black/55">{data.customer_email}</p>
                            </div>
                            <div>
                                <p className="text-xs uppercase tracking-wide text-black/55">Total</p>
                                <p className="text-lg font-bold text-black">{CURRENCY_FORMATTER.format(Number(data.total_amount))}</p>
                            </div>
                            <div>
                                <p className="text-xs uppercase tracking-wide text-black/55">Dibuat</p>
                                <p className="text-sm text-black">{DATE_FORMATTER.format(new Date(data.created_at))}</p>
                            </div>
                            <div>
                                <p className="text-xs uppercase tracking-wide text-black/55">Kedaluwarsa</p>
                                <p className="text-sm text-black">{data.expired_at ? DATE_FORMATTER.format(new Date(data.expired_at)) : '-'}</p>
                            </div>
                        </section>

                        {data.notes && (
                            <section>
                                <p className="text-xs uppercase tracking-wide text-black/55">Catatan Pelanggan</p>
                                <p className="text-sm text-black">{data.notes}</p>
                            </section>
                        )}

                        <section>
                            <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-black">
                                <Package className="size-4" /> Item Pesanan
                            </h3>
                            {data.items && data.items.length > 0 ? (
                                <div className="overflow-x-auto rounded-lg border border-black/10">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead className="text-black">Item</TableHead>
                                                <TableHead className="text-black">Tipe</TableHead>
                                                <TableHead className="text-right text-black">Qty</TableHead>
                                                <TableHead className="text-right text-black">Harga</TableHead>
                                                <TableHead className="text-right text-black">Subtotal</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {data.items.map((item: AdminOrderItem) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="text-black">{item.name}</TableCell>
                                                    <TableCell className="text-black/70">{item.item_type === 'product' ? 'Produk' : 'Event'}</TableCell>
                                                    <TableCell className="text-right text-black">{item.quantity}</TableCell>
                                                    <TableCell className="text-right text-black">{CURRENCY_FORMATTER.format(Number(item.unit_price))}</TableCell>
                                                    <TableCell className="text-right text-black">{CURRENCY_FORMATTER.format(Number(item.total_price))}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            ) : (
                                <p className="text-sm text-black/55">Tidak ada item pada pesanan ini.</p>
                            )}
                        </section>

                        <section>
                            <h3 className="mb-2 text-sm font-semibold text-black">Pembayaran</h3>
                            {data.payments && data.payments.length > 0 ? (
                                <div className="overflow-x-auto rounded-lg border border-black/10">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead className="text-black">ID</TableHead>
                                                <TableHead className="text-black">Metode</TableHead>
                                                <TableHead className="text-black">Gateway</TableHead>
                                                <TableHead className="text-black">Status</TableHead>
                                                <TableHead className="text-right text-black">Jumlah</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {data.payments.map((payment: AdminOrderPaymentRef) => (
                                                <TableRow key={payment.id}>
                                                    <TableCell className="font-mono text-xs text-black">{payment.id.slice(0, 8)}</TableCell>
                                                    <TableCell className="text-black">{payment.payment_method?.name ?? '-'}</TableCell>
                                                    <TableCell className="text-black">{payment.gateway}</TableCell>
                                                    <TableCell><PaymentStatusBadge status={payment.status} /></TableCell>
                                                    <TableCell className="text-right text-black">{CURRENCY_FORMATTER.format(Number(payment.amount))}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            ) : (
                                <p className="text-sm text-black/55">Belum ada pembayaran pada pesanan ini.</p>
                            )}
                        </section>
                    </div>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>
                        Tutup
                    </Button>
                    {data?.payments?.some((p) => p.gateway === 'manual' && p.status === 'pending') && (
                        <a href="/admin/payments" className="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-black hover:bg-black/5">
                            <ExternalLink className="size-3" /> Lihat di Pembayaran
                        </a>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
