import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { FilterOption } from '@/types/public';

type FilterChipsProps = {
    label: string;
    options: FilterOption[];
    value: string;
    onChange: (value: string) => void;
    className?: string;
};

export function FilterChips({ label, options, value, onChange, className }: FilterChipsProps) {
    return (
        <div
            role="group"
            aria-label={label}
            className={cn('flex gap-3 overflow-x-auto [scrollbar-width:none] md:flex-wrap md:overflow-visible', className)}
        >
            {options.map((option) => {
                const selected = option.value === value;

                return (
                    <Button
                        key={option.value}
                        variant="outline"
                        aria-pressed={selected}
                        onClick={() => onChange(option.value)}
                        className={cn(
                            'h-10 shrink-0 rounded-sm px-5 text-sm font-medium',
                            selected
                                ? 'border-primary bg-primary text-white hover:bg-primary/90 hover:text-white'
                                : 'bg-background text-muted-foreground/85 hover:border-primary hover:bg-accent hover:text-accent-foreground',
                        )}
                    >
                        {option.label}
                    </Button>
                );
            })}
        </div>
    );
}
