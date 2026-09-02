import { useEffect, useMemo, useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation, useQueries, useQuery, useQueryClient } from '@tanstack/react-query';
import { AlertCircle, CheckCircle2, Flame, Pencil, Plus, Search, Star, Trash2 } from 'lucide-react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Controller, useForm } from 'react-hook-form';
import { z } from 'zod';
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
import { MediaUploadField } from '@/components/admin/MediaUploadField';
import { NotionEditor } from '@/components/admin/NotionEditor';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { ApiError, getAdminData, sendAdminData } from '@/lib/admin-api';
import { cn } from '@/lib/utils';
import type { AdminRecord, ResourceConfig } from '@/types/admin';

type FormValue = string | number | boolean | null;
type FormState = Record<string, FormValue>;

const formValueSchema = z.union([z.string(), z.number(), z.boolean(), z.null()]);

function createResourceFormSchema(config: ResourceConfig, record: AdminRecord | null) {
    return z.record(z.string(), formValueSchema).superRefine((values, context) => {
        config.fields.forEach((field) => {
            const value = values[field.key];
            const required = Boolean(field.required && !(record && field.optionalOnEdit));

            if (required && (value === '' || value === null || value === undefined)) {
                context.addIssue({
                    code: 'custom',
                    path: [field.key],
                    message: `${field.label} wajib diisi.`,
                });
            }

            if (field.key === 'email' && typeof value === 'string' && value !== '' && !z.email().safeParse(value).success) {
                context.addIssue({ code: 'custom', path: [field.key], message: 'Format email tidak valid.' });
            }

            if (field.type === 'password' && typeof value === 'string' && value !== '' && value.length < 8) {
                context.addIssue({ code: 'custom', path: [field.key], message: 'Password minimal 8 karakter.' });
            }

            if (field.type === 'richtext' && required && typeof value === 'string' && value.replace(/<[^>]+>/g, '').trim() === '') {
                context.addIssue({ code: 'custom', path: [field.key], message: `${field.label} wajib diisi.` });
            }
        });
    });
}

function getPath(record: Record<string, unknown>, path: string): unknown {
    return path.split('.').reduce<unknown>((value, segment) => {
        if (value && typeof value === 'object' && segment in value) {
            return (value as Record<string, unknown>)[segment];
        }
        return undefined;
    }, record);
}

function initialForm(config: ResourceConfig, record: AdminRecord | null): FormState {
    return Object.fromEntries(config.fields.map((field) => {
        let value = record ? getPath(record, field.source ?? field.key) : undefined;

        if (field.type === 'boolean') value = value ?? false;
        if (field.type === 'select') value = value ?? field.options?.[0]?.value ?? '';
        if (field.type === 'number') value = value ?? '';
        if (field.type === 'datetime-local' && typeof value === 'string') value = value.slice(0, 16);

        return [field.key, (value ?? '') as FormValue];
    }));
}

function normalizedPayload(config: ResourceConfig, form: FormState): Record<string, FormValue> {
    return Object.fromEntries(config.fields.map((field) => {
        let value = form[field.key];
        if (field.type === 'number') value = value === '' ? null : Number(value);
        if (field.type === 'relation' && value === '') value = null;
        if (field.type === 'media' && value === '') value = null;
        if (field.type === 'password' && value === '') value = null;
        if (field.type === 'datetime-local' && value === '') value = null;
        return [field.key, value];
    }));
}

function formatStatus(value: unknown): string {
    return String(value ?? '-')
        .replace(/_/g, ' ')
        .toLowerCase()
        .replace(/\b\w/g, (character: string) => character.toUpperCase());
}

function CellValue({ value, type }: { value: unknown; type?: string }) {
    if (type === 'boolean') {
        return <span className="font-medium text-black">{value ? 'Ya' : 'Tidak'}</span>;
    }

    if (type === 'status') {
        return <span className="font-medium text-black">{formatStatus(value)}</span>;
    }

    if (type === 'currency') {
        return <span className="font-medium">{new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0))}</span>;
    }

    if (type === 'date') {
        return value
            ? <span className="whitespace-nowrap text-black/55">{new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(String(value)))}</span>
            : <span className="text-black/55">—</span>;
    }

    return <span className="line-clamp-2 max-w-xs">{String(value ?? '—')}</span>;
}

type ResourceFormProps = {
    config: ResourceConfig;
    record: AdminRecord | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSaved: (message: string) => void;
};

function ResourceForm({ config, record, open, onOpenChange, onSaved }: ResourceFormProps) {
    const queryClient = useQueryClient();
    const [submitError, setSubmitError] = useState('');
    const schema = useMemo(() => createResourceFormSchema(config, record), [config, record]);
    const {
        clearErrors,
        control,
        formState: { errors },
        handleSubmit,
        register,
        reset,
        setError,
    } = useForm<FormState>({
        defaultValues: initialForm(config, record),
        resolver: zodResolver(schema),
    });
    const relationFields = config.fields.filter((field) => field.type === 'relation' && field.relationUrl);
    const relationQueries = useQueries({
        queries: relationFields.map((field) => ({
            queryKey: ['admin', 'relation', field.relationUrl],
            queryFn: () => getAdminData<AdminRecord[]>(field.relationUrl!),
            staleTime: 60_000,
        })),
    });

    useEffect(() => {
        if (open) {
            reset(initialForm(config, record));
            clearErrors();
            setSubmitError('');
        }
    }, [clearErrors, config, open, record, reset]);

    const mutation = useMutation({
        mutationFn: (payload: Record<string, FormValue>) => record
            ? sendAdminData<AdminRecord>(`${config.endpoint}/${record.id}`, 'PATCH', payload)
            : sendAdminData<AdminRecord>(config.endpoint, 'POST', payload),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
            onOpenChange(false);
            onSaved(`${config.singular} berhasil ${record ? 'diperbarui' : 'ditambahkan'}.`);
        },
        onError: (error) => {
            if (error instanceof ApiError) {
                Object.entries(error.errors).forEach(([field, messages]) => {
                    setError(field, { type: 'server', message: messages[0] });
                });
                setSubmitError(error.message);
                return;
            }
            setSubmitError('Terjadi kesalahan saat menyimpan data.');
        },
    });

    const submit = (form: FormState) => {
        clearErrors();
        setSubmitError('');
        mutation.mutate(normalizedPayload(config, form));
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className={cn('max-h-[92vh] overflow-y-auto text-black [&_[data-slot=dialog-description]]:text-black/60 [&_[data-slot=dialog-title]]:text-black', config.key === 'articles' ? 'sm:max-w-6xl' : 'sm:max-w-3xl')}>
                <DialogHeader>
                    <DialogTitle>{record ? `Edit ${config.singular}` : `Tambah ${config.singular}`}</DialogTitle>
                    <DialogDescription>Lengkapi data berikut, lalu simpan perubahan.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit(submit)} className="space-y-5" noValidate>
                    {submitError && (
                        <Alert variant="destructive">
                            <AlertCircle />
                            <AlertTitle>Data belum tersimpan</AlertTitle>
                            <AlertDescription>{submitError}</AlertDescription>
                        </Alert>
                    )}
                    <div className="grid gap-5 sm:grid-cols-2">
                        {config.fields.map((resourceField) => {
                            const relationIndex = relationFields.findIndex((item) => item.key === resourceField.key);
                            const relationOptions = relationIndex >= 0 ? relationQueries[relationIndex]?.data ?? [] : [];
                            const fieldError = errors[resourceField.key];
                            const error = typeof fieldError?.message === 'string' ? fieldError.message : '';
                            const fieldId = `${config.key}-${resourceField.key}`;
                            const isRequired = Boolean(resourceField.required && !(record && resourceField.optionalOnEdit));

                            return (
                                <div key={resourceField.key} className={cn('space-y-2', resourceField.fullWidth && 'sm:col-span-2')}>
                                    <Label htmlFor={fieldId}>
                                        {resourceField.label}{isRequired && <span className="ml-1 text-destructive">*</span>}
                                    </Label>

                                    {resourceField.type === 'textarea' && (
                                        <Textarea
                                            id={fieldId}
                                            rows={4}
                                            placeholder={resourceField.placeholder}
                                            {...register(resourceField.key)}
                                            aria-invalid={Boolean(error)}
                                        />
                                    )}

                                    {['text', 'number', 'datetime-local', 'password'].includes(resourceField.type) && (
                                        <Input
                                            id={fieldId}
                                            type={resourceField.type === 'password' ? 'password' : resourceField.type}
                                            step={resourceField.type === 'number' ? 'any' : undefined}
                                            autoComplete={resourceField.type === 'password' ? 'new-password' : undefined}
                                            placeholder={resourceField.placeholder}
                                            {...register(resourceField.key)}
                                            aria-invalid={Boolean(error)}
                                        />
                                    )}

                                    {resourceField.type === 'richtext' && (
                                        <Controller
                                            name={resourceField.key}
                                            control={control}
                                            render={({ field }) => (
                                                <NotionEditor
                                                    value={String(field.value ?? '')}
                                                    onChange={field.onChange}
                                                    invalid={Boolean(error)}
                                                />
                                            )}
                                        />
                                    )}

                                    {resourceField.type === 'media' && (
                                        <Controller
                                            name={resourceField.key}
                                            control={control}
                                            render={({ field }) => (
                                                <MediaUploadField
                                                    id={fieldId}
                                                    initialUrl={String(record && resourceField.previewSource ? getPath(record, resourceField.previewSource) ?? '' : '')}
                                                    purpose={resourceField.mediaPurpose ?? 'general'}
                                                    onChange={field.onChange}
                                                    invalid={Boolean(error)}
                                                />
                                            )}
                                        />
                                    )}

                                    {resourceField.type === 'boolean' && (
                                        <Controller
                                            name={resourceField.key}
                                            control={control}
                                            render={({ field }) => (
                                                <div className="flex h-9 items-center justify-between rounded-lg border px-3">
                                                    <span className="text-sm text-black/60">{field.value ? 'Aktif' : 'Nonaktif'}</span>
                                                    <Switch id={fieldId} checked={Boolean(field.value)} onCheckedChange={field.onChange} />
                                                </div>
                                            )}
                                        />
                                    )}

                                    {resourceField.type === 'select' && (
                                        <Controller
                                            name={resourceField.key}
                                            control={control}
                                            render={({ field }) => (
                                                <Select value={String(field.value ?? '')} onValueChange={(next) => field.onChange(String(next))}>
                                                    <SelectTrigger id={fieldId} className="w-full">
                                                        <SelectValue placeholder={`Pilih ${resourceField.label.toLowerCase()}`}>
                                                            {resourceField.options?.find((option) => option.value === String(field.value))?.label}
                                                        </SelectValue>
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {resourceField.options?.map((option) => (
                                                            <SelectItem key={option.value} value={option.value}>{option.label}</SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                    )}

                                    {resourceField.type === 'relation' && (
                                        <Controller
                                            name={resourceField.key}
                                            control={control}
                                            render={({ field }) => (
                                                <Select
                                                    value={field.value === '' || field.value === null ? '__none__' : String(field.value)}
                                                    onValueChange={(next) => field.onChange(next === '__none__' ? '' : String(next))}
                                                >
                                                    <SelectTrigger id={fieldId} className="w-full">
                                                        <SelectValue placeholder={`Pilih ${resourceField.label.toLowerCase()}`}>
                                                            {field.value === '' || field.value === null
                                                                ? `Tanpa ${resourceField.label.toLowerCase()}`
                                                                : String(
                                                                    relationOptions.find((option) => String(option.id) === String(field.value))
                                                                        ?.[resourceField.relationLabel ?? 'name'] ?? 'Pilihan tersimpan',
                                                                )}
                                                        </SelectValue>
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="__none__">Tanpa {resourceField.label.toLowerCase()}</SelectItem>
                                                        {relationOptions.map((option) => (
                                                            <SelectItem key={option.id} value={String(option.id)}>
                                                                {String(option[resourceField.relationLabel ?? 'name'])}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                    )}

                                    {error && <p className="text-xs text-destructive">{error}</p>}
                                </div>
                            );
                        })}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Batal</Button>
                        <Button type="submit" disabled={mutation.isPending}>
                            {mutation.isPending ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function AdminCrudPage({ config }: { config: ResourceConfig }) {
    const queryClient = useQueryClient();
    const [search, setSearch] = useState('');
    const [formOpen, setFormOpen] = useState(false);
    const [selected, setSelected] = useState<AdminRecord | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<AdminRecord | null>(null);
    const [feedback, setFeedback] = useState('');
    const [page, setPage] = useState(1);
    const [paginationMeta, setPaginationMeta] = useState<{
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    } | null>(null);

    const searchParams = useMemo(() => {
        const params = new URLSearchParams();
        const keyword = search.trim();
        if (keyword) params.set('search', keyword);
        params.set('page', String(page));
        return params.size > 0 ? `?${params.toString()}` : '';
    }, [search, page]);

    const query = useQuery({
        queryKey: ['admin', 'resource', config.key, search, page],
        queryFn: () => getAdminData<AdminRecord[] | { data: AdminRecord[]; meta: typeof paginationMeta }>(`${config.endpoint}${searchParams}`),
    });

    const { rows, meta } = useMemo(() => {
        const raw = query.data;
        if (Array.isArray(raw)) {
            return { rows: raw, meta: null };
        }
        if (raw && typeof raw === 'object' && 'data' in raw) {
            const data = raw as { data: AdminRecord[]; meta: typeof paginationMeta };
            return { rows: data.data, meta: data.meta ?? null };
        }
        return { rows: [], meta: null };
    }, [query.data]);

    useEffect(() => {
        if (meta) {
            setPaginationMeta(meta);
        }
    }, [meta]);

    const deleteMutation = useMutation({
        mutationFn: (record: AdminRecord) => sendAdminData<void>(`${config.endpoint}/${record.id}`, 'DELETE'),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
            setFeedback(`${config.singular} berhasil dihapus.`);
            setDeleteTarget(null);
        },
    });

    const handleSearch = (value: string) => {
        setSearch(value);
        setPage(1);
    };

    const toggleMutation = useMutation({
        mutationFn: ({ record, field }: { record: AdminRecord; field: string }) =>
            sendAdminData<AdminRecord>(`${config.endpoint}/${record.id}/toggle`, 'PATCH', { field }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['admin', 'resource', config.key] });
        },
    });

    const openCreate = () => {
        setSelected(null);
        setFormOpen(true);
    };

    const openEdit = (record: AdminRecord) => {
        setSelected(record);
        setFormOpen(true);
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-black sm:text-3xl">{config.title}</h1>
                    <p className="mt-1 text-sm text-black/60">{config.description}</p>
                </div>
                <Button onClick={openCreate} size="lg"><Plus /> Tambah {config.singular}</Button>
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
                        <Input value={search} onChange={(event) => handleSearch(event.target.value)} placeholder="Cari data..." className="pl-9" />
                    </div>
                </CardHeader>
                <CardContent className="p-0">
                    {query.isLoading && (
                        <div className="space-y-3 p-6">
                            {[1, 2, 3, 4].map((item) => <Skeleton key={item} className="h-12 w-full" />)}
                        </div>
                    )}

                    {query.isError && (
                        <div className="p-6">
                            <Alert variant="destructive">
                                <AlertCircle />
                                <AlertTitle>Data gagal dimuat</AlertTitle>
                                <AlertDescription className="flex items-center justify-between gap-3">
                                    Periksa koneksi lalu coba kembali.
                                    <Button size="sm" variant="outline" onClick={() => query.refetch()}>Coba lagi</Button>
                                </AlertDescription>
                            </Alert>
                        </div>
                    )}

                    {query.isSuccess && (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        {config.columns.map((column) => <TableHead key={column.key} className="text-black">{column.label}</TableHead>)}
                                        <TableHead className="w-32 text-right text-black">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rows.map((record) => (
                                        <TableRow key={record.id}>
                                            {config.columns.map((column) => (
                                                <TableCell key={column.key}><CellValue value={getPath(record, column.key)} type={column.type} /></TableCell>
                                            ))}
                                            <TableCell>
                                                <div className="flex justify-end gap-1">
                                                    {config.key === 'products' && (
                                                        <>
                                                            <Button
                                                                variant="ghost"
                                                                size="icon-sm"
                                                                disabled={toggleMutation.isPending}
                                                                onClick={() => toggleMutation.mutate({ record, field: 'is_featured' })}
                                                                aria-label="Toggle unggulan"
                                                                title="Toggle unggulan"
                                                                className={getPath(record, 'is_featured') ? 'text-primary' : 'text-black/30'}
                                                            >
                                                                <Star />
                                                            </Button>
                                                            <Button
                                                                variant="ghost"
                                                                size="icon-sm"
                                                                disabled={toggleMutation.isPending}
                                                                onClick={() => toggleMutation.mutate({ record, field: 'is_best_seller' })}
                                                                aria-label="Toggle terlaris"
                                                                title="Toggle terlaris"
                                                                className={getPath(record, 'is_best_seller') ? 'text-primary' : 'text-black/30'}
                                                            >
                                                                <Flame />
                                                            </Button>
                                                        </>
                                                    )}
                                                    <Button variant="ghost" size="icon-sm" onClick={() => openEdit(record)} aria-label={`Edit ${config.singular}`}><Pencil /></Button>
                                                    <Button variant="ghost" size="icon-sm" onClick={() => setDeleteTarget(record)} aria-label={`Hapus ${config.singular}`} className="text-destructive"><Trash2 /></Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {rows.length === 0 && (
                                        <TableRow><TableCell colSpan={config.columns.length + 1} className="h-32 text-center text-black/55">Belum ada data yang sesuai.</TableCell></TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    )}

                    {paginationMeta && paginationMeta.last_page > 1 && (
                        <div className="flex items-center justify-between border-t px-6 py-4">
                            <p className="text-sm text-black/55">
                                Menampilkan {(paginationMeta.current_page - 1) * paginationMeta.per_page + 1}–
                                {Math.min(paginationMeta.current_page * paginationMeta.per_page, paginationMeta.total)} dari {paginationMeta.total} data
                            </p>
                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={paginationMeta.current_page <= 1 || query.isFetching}
                                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                                >
                                    <ChevronLeft /> Sebelumnya
                                </Button>
                                <span className="text-sm font-medium text-black/70">
                                    {paginationMeta.current_page} / {paginationMeta.last_page}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={paginationMeta.current_page >= paginationMeta.last_page || query.isFetching}
                                    onClick={() => setPage((p) => Math.min(paginationMeta.last_page, p + 1))}
                                >
                                    Berikutnya <ChevronRight />
                                </Button>
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>

            <ResourceForm config={config} record={selected} open={formOpen} onOpenChange={setFormOpen} onSaved={setFeedback} />

            <AlertDialog open={Boolean(deleteTarget)} onOpenChange={(open) => !open && setDeleteTarget(null)}>
                <AlertDialogContent className="text-black [&_[data-slot=alert-dialog-description]]:text-black/60 [&_[data-slot=alert-dialog-title]]:text-black">
                    <AlertDialogHeader>
                        <AlertDialogTitle>Hapus {config.singular}?</AlertDialogTitle>
                        <AlertDialogDescription>Data yang sudah dihapus tidak dapat dipulihkan melalui dashboard.</AlertDialogDescription>
                    </AlertDialogHeader>
                    {deleteMutation.isError && <p className="text-sm text-destructive">Data gagal dihapus. Pastikan data tidak sedang digunakan.</p>}
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction
                            variant="destructive"
                            disabled={deleteMutation.isPending}
                            onClick={() => deleteTarget && deleteMutation.mutate(deleteTarget)}
                        >
                            {deleteMutation.isPending ? 'Menghapus...' : 'Hapus'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
