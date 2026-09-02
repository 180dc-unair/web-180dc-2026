import { Head, Link } from '@inertiajs/react';
import { Bar, BarChart, CartesianGrid, XAxis } from 'recharts';
import {
    ArrowRight,
    BookOpenText,
    BriefcaseBusiness,
    Handshake,
    MessageSquareText,
    Package,
    Plus,
} from 'lucide-react';
import { AdminLayout } from '@/components/admin/AdminLayout';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useDashboardSummary } from '@/hooks/admin/useDashboard';
import type { DashboardSummary } from '@/services/admin/dashboard.service';

const activityChartConfig = {
    articles: { label: 'Artikel', color: '#72BB0E' },
    comments: { label: 'Komentar', color: '#111827' },
} satisfies ChartConfig;

const metricCards = [
    { key: 'products', label: 'Total Produk', detailKey: 'active', detailLabel: 'aktif', icon: Package, href: '/admin/products' },
    { key: 'articles', label: 'Total Artikel', detailKey: 'published', detailLabel: 'terbit', icon: BookOpenText, href: '/admin/articles' },
    { key: 'services', label: 'Total Layanan', detailKey: 'active', detailLabel: 'aktif', icon: BriefcaseBusiness, href: '/admin/services' },
    { key: 'clients', label: 'Klien & Partner', detailKey: 'featured', detailLabel: 'unggulan', icon: Handshake, href: '/admin/clients' },
    { key: 'comments', label: 'Total Komentar', detailKey: 'pending', detailLabel: 'menunggu', icon: MessageSquareText, href: '/admin/comments' },
] as const;

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value));
}

export default function AdminDashboard() {
    const summary = useDashboardSummary();

    return (
        <AdminLayout title="Dashboard" description="Ringkasan aktivitas dan konten 180DC UNAIR">
            <Head title="Dashboard Admin" />
            <div className="space-y-7">
                <section className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-black sm:text-3xl">Selamat datang di dashboard</h1>
                        <p className="mt-1 text-sm text-black/60">Pantau data utama dan lanjutkan pekerjaan yang perlu diperbarui.</p>
                    </div>
                    <Link href="/admin/articles" className={buttonVariants({ size: 'lg' })}><Plus /> Buat Artikel</Link>
                </section>

                {summary.isError && (
                    <Alert variant="destructive">
                        <AlertTitle>Ringkasan gagal dimuat</AlertTitle>
                        <AlertDescription className="flex items-center justify-between gap-4">
                            Silakan coba kembali.
                            <Button variant="outline" size="sm" onClick={() => summary.refetch()}>Muat ulang</Button>
                        </AlertDescription>
                    </Alert>
                )}

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Ringkasan data">
                    {metricCards.map((metric) => {
                        const Icon = metric.icon;
                        const values = summary.data?.metrics[metric.key];
                        const detail = values
                            ? (values as Record<string, number>)[metric.detailKey]
                            : 0;
                        return (
                            <Link key={metric.key} href={metric.href} className="group">
                                <Card className="h-full transition-all hover:-translate-y-0.5 hover:border-black/20 hover:shadow-md">
                                    <CardContent className="p-5">
                                        <div className="flex items-start justify-between">
                                            <div className="grid size-10 place-items-center rounded-xl bg-black/5 text-black"><Icon className="size-5" /></div>
                                            <ArrowRight className="size-4 text-black/40 transition-transform group-hover:translate-x-1 group-hover:text-black" />
                                        </div>
                                        {summary.isLoading ? <Skeleton className="mt-5 h-9 w-20" /> : <p className="mt-5 text-3xl font-bold text-black">{values?.total ?? 0}</p>}
                                        <p className="mt-1 text-sm font-medium text-black">{metric.label}</p>
                                        <p className="mt-1 text-xs text-black/55">{detail ?? 0} {metric.detailLabel}</p>
                                    </CardContent>
                                </Card>
                            </Link>
                        );
                    })}
                </section>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base text-black">Aktivitas 7 Hari Terakhir</CardTitle>
                        <CardDescription className="text-black/60">Artikel dan komentar baru per hari.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {summary.isLoading ? (
                            <Skeleton className="h-72 w-full" />
                        ) : (
                            <ChartContainer config={activityChartConfig} className="h-72 w-full">
                                <BarChart accessibilityLayer data={summary.data?.activity ?? []} margin={{ left: 4, right: 4 }}>
                                    <CartesianGrid vertical={false} />
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={10} />
                                    <ChartTooltip cursor={false} content={<ChartTooltipContent indicator="dot" />} />
                                    <Bar dataKey="articles" fill="var(--color-articles)" radius={[5, 5, 0, 0]} />
                                    <Bar dataKey="comments" fill="var(--color-comments)" radius={[5, 5, 0, 0]} />
                                </BarChart>
                            </ChartContainer>
                        )}
                    </CardContent>
                </Card>

                <section className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between">
                            <div>
                                <CardTitle className="text-base text-black">Artikel Terbaru</CardTitle>
                                <CardDescription className="text-black/60">Konten yang terakhir dibuat.</CardDescription>
                            </div>
                            <Link href="/admin/articles" className={buttonVariants({ variant: 'ghost', size: 'sm' })}>Lihat semua <ArrowRight /></Link>
                        </CardHeader>
                        <CardContent className="p-0">
                            {summary.isLoading ? (
                                <div className="space-y-3 p-6">{[1, 2, 3].map((item) => <Skeleton key={item} className="h-11 w-full" />)}</div>
                            ) : (
                                <Table>
                                    <TableHeader><TableRow><TableHead className="text-black">Judul</TableHead><TableHead className="text-black">Status</TableHead><TableHead className="text-black">Tanggal</TableHead></TableRow></TableHeader>
                                    <TableBody>
                                        {summary.data?.recent.articles.map((article) => (
                                            <TableRow key={article.id}>
                                                <TableCell className="max-w-sm font-medium"><span className="line-clamp-1">{article.title}</span></TableCell>
                                                <TableCell className="font-medium text-black">{article.status}</TableCell>
                                                <TableCell className="whitespace-nowrap text-black/55">{formatDate(article.created_at)}</TableCell>
                                            </TableRow>
                                        ))}
                                        {!summary.data?.recent.articles.length && <TableRow><TableCell colSpan={3} className="h-28 text-center text-black/55">Belum ada artikel.</TableCell></TableRow>}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex-row items-center justify-between">
                            <div>
                                <CardTitle className="text-base text-black">Komentar Terbaru</CardTitle>
                                <CardDescription className="text-black/60">Masukan terbaru dari pembaca.</CardDescription>
                            </div>
                            <Link href="/admin/comments" className={buttonVariants({ variant: 'ghost', size: 'sm' })}>Kelola <ArrowRight /></Link>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {summary.isLoading && [1, 2, 3].map((item) => <Skeleton key={item} className="h-16 w-full" />)}
                            {summary.data?.recent.comments.map((comment) => (
                                <div key={comment.id} className="flex gap-3 border-b pb-4 last:border-0 last:pb-0">
                                    <span className="mt-1 size-2 shrink-0 rounded-full bg-black" />
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center justify-between gap-2">
                                            <p className="truncate text-sm font-semibold text-black">{comment.user ?? 'Pengguna'}</p>
                                            <span className="text-xs font-medium text-black">{comment.is_approved ? 'Disetujui' : 'Menunggu'}</span>
                                        </div>
                                        <p className="mt-1 line-clamp-2 text-xs text-black/55">{comment.content}</p>
                                        <p className="mt-1 truncate text-xs text-black/65">{comment.article ?? 'Artikel'}</p>
                                    </div>
                                </div>
                            ))}
                            {!summary.isLoading && !summary.data?.recent.comments.length && <p className="py-8 text-center text-sm text-black/55">Belum ada komentar.</p>}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </AdminLayout>
    );
}
