import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { FilterOption } from '@/types/public';

type SortSelectProps = {
    options: FilterOption[];
    value: string;
    onChange: (value: string) => void;
};

export function SortSelect({ options, value, onChange }: SortSelectProps) {
    const selected = options.find((option) => option.value === value);

    return (
        <Select value={value} onValueChange={(next) => onChange(String(next))}>
            <SelectTrigger
                aria-label="Sort"
                className="h-10 shrink-0 gap-2.5 rounded-sm border-border bg-background px-5 text-sm data-[size=default]:h-10"
            >
                <span className="text-muted-foreground/85">Sort:</span>
                <SelectValue className="font-semibold text-muted-foreground">{selected?.label}</SelectValue>
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
