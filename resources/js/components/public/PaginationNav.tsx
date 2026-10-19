import { Button } from '@/components/ui/button';
import { getPageItems } from '@/lib/pagination';
import { cn } from '@/lib/utils';

type PaginationNavProps = {
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
};

const pageButtonClassName = 'h-11 min-w-11 rounded-lg border-border bg-background text-sm font-medium text-muted-foreground/85';

export function PaginationNav({ currentPage, totalPages, onPageChange }: PaginationNavProps) {
    if (totalPages <= 1) {
        return null;
    }

    return (
        <nav aria-label="Pagination" className="flex flex-wrap items-center justify-center gap-2">
            <Button
                variant="outline"
                className={cn(pageButtonClassName, 'px-5')}
                disabled={currentPage === 1}
                onClick={() => onPageChange(currentPage - 1)}
            >
                ← Previous
            </Button>

            {getPageItems(currentPage, totalPages).map((item, index) =>
                item === 'ellipsis' ? (
                    <span
                        key={`ellipsis-${index}`}
                        aria-hidden="true"
                        className={cn(pageButtonClassName, 'inline-flex items-center justify-center border')}
                    >
                        …
                    </span>
                ) : (
                    <Button
                        key={item}
                        variant="outline"
                        aria-label={`Page ${item}`}
                        aria-current={item === currentPage ? 'page' : undefined}
                        onClick={() => onPageChange(item)}
                        className={cn(
                            pageButtonClassName,
                            item === currentPage &&
                                'border-primary bg-primary font-bold text-white hover:bg-primary/90 hover:text-white',
                        )}
                    >
                        {item}
                    </Button>
                ),
            )}

            <Button
                variant="outline"
                className={cn(pageButtonClassName, 'px-5')}
                disabled={currentPage === totalPages}
                onClick={() => onPageChange(currentPage + 1)}
            >
                Next →
            </Button>
        </nav>
    );
}
