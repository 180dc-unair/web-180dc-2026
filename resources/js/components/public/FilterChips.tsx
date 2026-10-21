import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { FilterOption } from '@/types/public';

const chipTones = {
    default: {
        base: 'h-10 rounded-sm px-5 text-sm font-medium',
        selected: 'border-primary bg-primary text-white hover:bg-primary/90 hover:text-white',
        idle: 'bg-background text-muted-foreground/85 hover:border-primary hover:bg-accent hover:text-accent-foreground',
    },
    onPrimary: {
        base: 'h-8 rounded-full px-4 text-xs font-semibold',
        selected: 'border-muted-foreground bg-muted-foreground text-white hover:bg-muted-foreground/90 hover:text-white',
        idle: 'border-transparent bg-white text-muted-foreground hover:bg-accent hover:text-accent-foreground',
    },
};

type FilterChipsProps = {
    label: string;
    options: FilterOption[];
    value: string;
    onChange: (value: string) => void;
    className?: string;
    tone?: keyof typeof chipTones;
};

export function FilterChips({ label, options, value, onChange, className, tone = 'default' }: FilterChipsProps) {
    const styles = chipTones[tone];

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
                        className={cn('shrink-0', styles.base, selected ? styles.selected : styles.idle)}
                    >
                        {option.label}
                    </Button>
                );
            })}
        </div>
    );
}