import { AlertCircle, ExternalLink, ImageIcon, Wallet } from 'lucide-react';
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
import type { AdminPayment } from '@/services/admin/payments.service';
import { usePaymentQuery } from '@/hooks/admin/usePayments';
import { ApiError } from '@/lib/admin-api';

const CURRENCY_FORMATTER = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
const DATE_FORMATTER = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' });

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

function StatusBadge({ status }: { status: string }) {
    return <Badge variant="outline" className="text-black">{STATUS_LABEL[status] ?? status}</Badge>;
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <section className="space-y-2">
            <h3 className="text-sm font-semibold text-black">{title}</h3>
            <div className="space-y-1 text-sm text-black">{children}</div>
        </section>
    );
}

function FieldRow({ label, value, mono }: { label: string; value: React.ReactNode; mono?: boolean }) {
    return (
        <div className="grid grid-cols-3 gap-2">
            <span className="text-xs uppercase tracking-wide text-black/55">{label}</span>
            <span className={`col-span-2 ${mono ? 'font-mono text-xs' : ''} text-black`}>{value}</span>
        </div>
    );
}

export function PaymentDetailDialog({
    paymentId,
    open,
    onOpenChange,
}: {
    paymentId: string | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const payment = usePaymentQuery(open ? paymentId : null);

    const data: AdminPayment | undefined = payment.data;

    const error = payment.error instanceof ApiError ? payment.error.message : payment.error instanceof Error ? payment.error.message : null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[92vh] overflow-y-auto text-black sm:max-w-2xl [&_[data-slot=dialog-description]]:text-black/60 [&_[data-slot=dialog-title]]:text-black">
                <DialogHeader>
                    <DialogTitle>Detail Pembayaran</DialogTitle>
                    <DialogDescription>Ringkasan pembayaran, instruksi, dan bukti transfer.</DialogDescription>
                </DialogHeader>

                {payment.isLoading && (
                    <div className="space-y-3">
                        <Skeleton className="h-6 w-1/2" />
                        <Skeleton className="h-32 w-full" />
                        <Skeleton className="h-24 w-full" />
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
                    <div className="space-y-5">
                        <Section title="Identitas">
                            <FieldRow label="ID Pembayaran" value={data.id.slice(0, 12)} mono />
                            <FieldRow label="Status" value={<StatusBadge status={data.status} />} />
                            <FieldRow label="Gateway" value={GATEWAY_LABEL[data.gateway] ?? data.gateway} />
                            <FieldRow label="Metode" value={data.payment_method?.name ?? '-'} />
                            <FieldRow label="Jumlah" value={CURRENCY_FORMATTER.format(Number(data.amount))} />
                            <FieldRow label="Referensi" value={data.gateway_reference ?? '-'} mono />
                        </Section>

                        <Section title="Pesanan">
                            <FieldRow label="Nomor Pesanan" value={data.order?.order_number ?? '-'} mono />
                            <FieldRow label="Status Pesanan" value={data.order?.status ?? '-'} />
                            <FieldRow label="Total Pesanan" value={data.order ? CURRENCY_FORMATTER.format(Number(data.order.total_amount)) : '-'} />
                        </Section>

                        <Section title="Waktu">
                            <FieldRow label="Dibuat" value={DATE_FORMATTER.format(new Date(data.created_at))} />
                            <FieldRow label="Kedaluwarsa" value={data.expired_at ? DATE_FORMATTER.format(new Date(data.expired_at)) : '-'} />
                            <FieldRow label="Dibayar" value={data.paid_at ? DATE_FORMATTER.format(new Date(data.paid_at)) : '-'} />
                        </Section>

                        {data.payment_url && (
                            <Section title="Tautan Pembayaran">
                                <a href={data.payment_url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 text-sm text-black underline">
                                    <ExternalLink className="size-3" /> {data.payment_url}
                                </a>
                            </Section>
                        )}

                        {data.instructions && (
                            <Section title="Instruksi Pembayaran">
                                <div className="rounded-md border border-black/10 bg-black/5 p-3 text-sm text-black">
                                    {data.instructions.bank && <FieldRow label="Bank" value={data.instructions.bank} />}
                                    {data.instructions.account_number && <FieldRow label="No. Rekening" value={data.instructions.account_number} mono />}
                                    {data.instructions.account_name && <FieldRow label="Atas Nama" value={data.instructions.account_name} />}
                                    {data.instructions.va_number && <FieldRow label="VA Number" value={data.instructions.va_number} mono />}
                                    {data.instructions.note && <FieldRow label="Catatan" value={data.instructions.note} />}
                                </div>
                            </Section>
                        )}

                        {data.proof && (
                            <Section title="Bukti Pembayaran">
                                <a href={data.proof.url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-2 text-sm text-black underline">
                                    <ImageIcon className="size-3" /> Lihat gambar bukti
                                </a>
                            </Section>
                        )}
                    </div>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>
                        Tutup
                    </Button>
                    {data?.gateway === 'manual' && data.status === 'pending' && (
                        <a href="/admin/payments" className="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-black hover:bg-black/5">
                            <Wallet className="size-3" /> Konfirmasi di Pembayaran
                        </a>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
