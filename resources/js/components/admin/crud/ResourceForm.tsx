import { useEffect, useMemo, useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { useQueries } from '@tanstack/react-query';
import { AlertCircle } from 'lucide-react';
import { Controller, useForm } from 'react-hook-form';
import { z } from 'zod';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { MediaUploadField } from '@/components/admin/MediaUploadField';
import { NotionEditor } from '@/components/admin/NotionEditor';
import { Button } from '@/components/ui/button';
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
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { ApiError } from '@/lib/admin-api';
import { cn } from '@/lib/utils';
import { resourceService } from '@/services/admin/resource.service';
import type { AdminRecord, ResourceConfig } from '@/types/admin';
import { getPath, initialForm, normalizedPayload, type FormState, type FormValue } from './resource-utils';

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

type ResourceFormProps = {
    config: ResourceConfig;
    record: AdminRecord | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSaved: (message: string) => void;
};

export function ResourceForm({ config, record, open, onOpenChange, onSaved }: ResourceFormProps) {
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
            queryFn: () => resourceService.fetchRelation(field.relationUrl!),
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

    const handleSave = async (form: FormState) => {
        clearErrors();
        setSubmitError('');
        const payload = normalizedPayload(config, form);
        try {
            if (record) {
                await resourceService.update(config.endpoint, record.id, payload);
            } else {
                await resourceService.create(config.endpoint, payload);
            }
            onOpenChange(false);
            onSaved(`${config.singular} berhasil ${record ? 'diperbarui' : 'ditambahkan'}.`);
        } catch (error) {
            if (error instanceof ApiError) {
                Object.entries(error.errors).forEach(([field, messages]) => {
                    setError(field, { type: 'server', message: messages[0] });
                });
                setSubmitError(error.message);
                return;
            }
            setSubmitError('Terjadi kesalahan saat menyimpan data.');
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className={cn('max-h-[92vh] overflow-y-auto text-black [&_[data-slot=dialog-description]]:text-black/60 [&_[data-slot=dialog-title]]:text-black', config.key === 'articles' ? 'sm:max-w-6xl' : 'sm:max-w-3xl')}>
                <DialogHeader>
                    <DialogTitle>{record ? `Edit ${config.singular}` : `Tambah ${config.singular}`}</DialogTitle>
                    <DialogDescription>Lengkapi data berikut, lalu simpan perubahan.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit(handleSave)} className="space-y-5" noValidate>
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
                        <Button type="submit">
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
