import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { PaginationMeta } from '@/types/pagination';

type ResourcePaginationProps = {
    meta: PaginationMeta;
    isFetching: boolean;
    onPrev: () => void;
    onNext: () => void;
};

export function ResourcePagination({ meta, isFetching, onPrev, onNext }: ResourcePaginationProps) {
    if (meta.last_page <= 1) return null;

    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to = Math.min(meta.current_page * meta.per_page, meta.total);

    return (
        <div className="flex items-center justify-between border-t px-6 py-4">
            <p className="text-sm text-black/55">
                Menampilkan {from}–{to} dari {meta.total} data
            </p>
            <div className="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    disabled={meta.current_page <= 1 || isFetching}
                    onClick={onPrev}
                >
                    <ChevronLeft /> Sebelumnya
                </Button>
                <span className="text-sm font-medium text-black/70">
                    {meta.current_page} / {meta.last_page}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    disabled={meta.current_page >= meta.last_page || isFetching}
                    onClick={onNext}
                >
                    Berikutnya <ChevronRight />
                </Button>
            </div>
        </div>
    );
}
