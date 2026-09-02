import { Head } from '@inertiajs/react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { AlertCircle, Check, CheckCircle2, MessageSquareText, Search, Trash2, Undo2 } from 'lucide-react';
import { useState } from 'react';
import { AdminLayout } from '@/components/admin/AdminLayout';
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
import { getAdminData, sendAdminData } from '@/lib/admin-api';

type ArticleComment = {
    id: number;
    content: string;
    is_approved: boolean;
    user: { id: number; name: string; username: string } | null;
    article: { id: number; title: string; slug: string } | null;
    created_at: string;
};

export default function AdminComments() {
    const queryClient = useQueryClient();
    const [search, setSearch] = useState('');
    const [filter, setFilter] = useState('all');
    const [deleteTarget, setDeleteTarget] = useState<ArticleComment | null>(null);
    const [feedback, setFeedback] = useState('');
    const params = new URLSearchParams();
    if (search.trim()) params.set('search', search.trim());
    if (filter !== 'all') params.set('is_approved', filter === 'approved' ? '1' : '0');
    const url = `/api/admin/article-comments${params.size ? `?${params.toString()}` : ''}`;

    const comments = useQuery({
        queryKey: ['admin', 'comments', search, filter],
        queryFn: () => getAdminData<ArticleComment[]>(url),
    });

    const moderate = useMutation({
        mutationFn: (comment: ArticleComment) => sendAdminData<ArticleComment>(
            `/api/article-comments/${comment.id}/moderate`,
            'PATCH',
            { is_approved: !comment.is_approved },
        ),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin', 'comments'] });
            setFeedback('Status komentar berhasil diperbarui.');
        },
    });

    const remove = useMutation({
        mutationFn: (comment: ArticleComment) => sendAdminData<void>(`/api/article-comments/${comment.id}`, 'DELETE'),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'comments'] });
            setDeleteTarget(null);
        },
    });

    return (
        <AdminLayout title="Komentar" description="Kelola komentar pada artikel">
            <Head title="Komentar - Admin" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-black sm:text-3xl">Komentar Artikel</h1>
                    <p className="mt-1 text-sm text-black/60">Tinjau, setujui, atau hapus komentar pembaca.</p>
                </div>

                {(comments.isError || moderate.isError || remove.isError) && (
                    <Alert variant="destructive">
                        <AlertCircle />
                        <AlertTitle>Operasi gagal</AlertTitle>
                        <AlertDescription>Data tidak dapat diproses. Silakan coba kembali.</AlertDescription>
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
                        <CardTitle className="flex items-center gap-2 text-base text-black"><MessageSquareText className="size-4" /> Daftar Komentar</CardTitle>
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <div className="relative sm:w-72">
                                <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-black/45" />
                                <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Cari isi komentar..." className="pl-9" />
                            </div>
                            <Select value={filter} onValueChange={(value) => setFilter(String(value))}>
                                <SelectTrigger className="w-full sm:w-44">
                                    <SelectValue>
                                        {{ all: 'Semua status', pending: 'Menunggu', approved: 'Disetujui' }[filter]}
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua status</SelectItem>
                                    <SelectItem value="pending">Menunggu</SelectItem>
                                    <SelectItem value="approved">Disetujui</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {comments.isLoading ? <div className="space-y-3 p-6">{[1, 2, 3, 4].map((item) => <Skeleton key={item} className="h-14 w-full" />)}</div> : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader><TableRow><TableHead>Komentar</TableHead><TableHead>Pengguna</TableHead><TableHead>Artikel</TableHead><TableHead>Status</TableHead><TableHead>Tanggal</TableHead><TableHead className="text-right">Aksi</TableHead></TableRow></TableHeader>
                                    <TableBody>
                                        {comments.data?.map((comment) => (
                                            <TableRow key={comment.id}>
                                                <TableCell><p className="line-clamp-3 min-w-64 max-w-md text-sm">{comment.content}</p></TableCell>
                                                <TableCell className="whitespace-nowrap"><p className="font-medium">{comment.user?.name ?? 'Pengguna'}</p><p className="text-xs text-black/55">@{comment.user?.username ?? '-'}</p></TableCell>
                                                <TableCell><p className="line-clamp-2 min-w-40 max-w-xs">{comment.article?.title ?? 'Artikel dihapus'}</p></TableCell>
                                                <TableCell className="font-medium text-black">{comment.is_approved ? 'Disetujui' : 'Menunggu'}</TableCell>
                                                <TableCell className="whitespace-nowrap text-black/55">{new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(comment.created_at))}</TableCell>
                                                <TableCell>
                                                    <div className="flex justify-end gap-1">
                                                        <Button
                                                            variant="ghost" size="icon-sm"
                                                            onClick={() => moderate.mutate(comment)}
                                                            disabled={moderate.isPending}
                                                            aria-label={comment.is_approved ? 'Batalkan persetujuan' : 'Setujui komentar'}
                                                        >{comment.is_approved ? <Undo2 /> : <Check />}</Button>
                                                        <Button variant="ghost" size="icon-sm" className="text-destructive" onClick={() => setDeleteTarget(comment)} aria-label="Hapus komentar"><Trash2 /></Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                        {!comments.data?.length && <TableRow><TableCell colSpan={6} className="h-32 text-center text-black/55">Belum ada komentar yang sesuai.</TableCell></TableRow>}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            <AlertDialog open={Boolean(deleteTarget)} onOpenChange={(open) => !open && setDeleteTarget(null)}>
                <AlertDialogContent className="text-black [&_[data-slot=alert-dialog-description]]:text-black/60 [&_[data-slot=alert-dialog-title]]:text-black">
                    <AlertDialogHeader><AlertDialogTitle>Hapus komentar?</AlertDialogTitle><AlertDialogDescription>Komentar akan dihapus permanen beserta balasannya.</AlertDialogDescription></AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction variant="destructive" disabled={remove.isPending} onClick={() => deleteTarget && remove.mutate(deleteTarget)}>{remove.isPending ? 'Menghapus...' : 'Hapus'}</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AdminLayout>
    );
}
