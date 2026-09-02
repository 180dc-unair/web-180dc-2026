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
import type { AdminRecord, ResourceConfig } from '@/types/admin';

type DeleteDialogProps = {
    config: ResourceConfig;
    target: AdminRecord | null;
    isPending: boolean;
    isError: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: (record: AdminRecord) => void;
};

export function DeleteDialog({
    config,
    target,
    isPending,
    isError,
    onOpenChange,
    onConfirm,
}: DeleteDialogProps) {
    return (
        <AlertDialog open={Boolean(target)} onOpenChange={onOpenChange}>
            <AlertDialogContent className="text-black [&_[data-slot=alert-dialog-description]]:text-black/60 [&_[data-slot=alert-dialog-title]]:text-black">
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus {config.singular}?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Data yang sudah dihapus tidak dapat dipulihkan melalui dashboard.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                {isError && (
                    <p className="text-sm text-destructive">
                        Data gagal dihapus. Pastikan data tidak sedang digunakan.
                    </p>
                )}
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction
                        variant="destructive"
                        disabled={isPending}
                        onClick={() => target && onConfirm(target)}
                    >
                        {isPending ? 'Menghapus...' : 'Hapus'}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
