import { useEffect, useRef, useState } from 'react';
import { ImageIcon, Loader2, UploadCloud, X } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { ApiError, uploadAdminMedia } from '@/lib/admin-api';
import type { MediaPurpose } from '@/types/admin';

type MediaUploadFieldProps = {
    id: string;
    initialUrl?: string;
    purpose: MediaPurpose;
    onChange: (mediaId: string) => void;
    invalid?: boolean;
};

export function MediaUploadField({
    id,
    initialUrl,
    purpose,
    onChange,
    invalid = false,
}: MediaUploadFieldProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [previewUrl, setPreviewUrl] = useState(initialUrl ?? '');
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        setPreviewUrl(initialUrl ?? '');
        setError('');
    }, [initialUrl]);

    const upload = async (file?: File) => {
        if (!file) return;

        setUploading(true);
        setError('');

        try {
            const media = await uploadAdminMedia(file, purpose);
            setPreviewUrl(media.url);
            onChange(media.id);
        } catch (uploadError) {
            setError(uploadError instanceof ApiError ? uploadError.message : 'Gambar gagal diunggah.');
        } finally {
            setUploading(false);
            if (inputRef.current) inputRef.current.value = '';
        }
    };

    const remove = () => {
        setPreviewUrl('');
        setError('');
        onChange('');
    };

    return (
        <div
            className="overflow-hidden rounded-xl border bg-background"
            data-invalid={invalid || undefined}
        >
            <input
                ref={inputRef}
                id={id}
                type="file"
                accept="image/jpeg,image/png,image/webp,image/gif"
                className="sr-only"
                onChange={(event) => void upload(event.target.files?.[0])}
            />

            {previewUrl ? (
                <div className="relative aspect-[16/7] bg-muted">
                    <img src={previewUrl} alt="Pratinjau media" className="size-full object-cover" />
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon-sm"
                        className="absolute right-3 top-3 shadow-sm"
                        onClick={remove}
                        aria-label="Hapus gambar dari form"
                    >
                        <X />
                    </Button>
                </div>
            ) : (
                <button
                    type="button"
                    className="flex min-h-40 w-full flex-col items-center justify-center gap-3 border-0 bg-muted/25 px-6 py-8 text-center text-black transition-colors hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    onClick={() => inputRef.current?.click()}
                    disabled={uploading}
                >
                    <span className="grid size-11 place-items-center rounded-xl border bg-background">
                        {uploading ? <Loader2 className="size-5 animate-spin" /> : <ImageIcon className="size-5" />}
                    </span>
                    <span>
                        <span className="block text-sm font-semibold">
                            {uploading ? 'Mengunggah gambar...' : 'Pilih gambar dari perangkat'}
                        </span>
                        <span className="mt-1 block text-xs text-black/55">JPG, PNG, WebP, atau GIF · maksimal 10 MB</span>
                    </span>
                </button>
            )}

            {previewUrl && (
                <div className="flex items-center justify-between gap-3 border-t px-4 py-3">
                    <div className="min-w-0">
                        <p className="text-sm font-medium text-black">Gambar siap digunakan</p>
                        <p className="truncate text-xs text-black/55">Media terhubung otomatis ke data ini.</p>
                    </div>
                    <Button type="button" variant="outline" size="sm" onClick={() => inputRef.current?.click()} disabled={uploading}>
                        {uploading ? <Loader2 className="animate-spin" /> : <UploadCloud />}
                        Ganti
                    </Button>
                </div>
            )}

            {error && (
                <Alert variant="destructive" className="m-3 w-auto">
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

        </div>
    );
}
