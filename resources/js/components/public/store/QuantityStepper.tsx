import type { Dispatch, SetStateAction } from 'react';
import { Minus, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

type QuantityStepperProps = {
    value: number;
    max: number;
    onChange: Dispatch<SetStateAction<number>>;
};

export function QuantityStepper({ value, max, onChange }: QuantityStepperProps) {
    return (
        <div className="flex h-11 shrink-0 items-center gap-1 rounded-sm border border-muted-foreground/25 px-1.5">
            <Button
                variant="ghost"
                size="icon-sm"
                aria-label="Kurangi jumlah"
                disabled={value <= 1}
                onClick={() => onChange((current) => Math.max(current - 1, 1))}
                className="text-muted-foreground/85"
            >
                <Minus />
            </Button>
            <span aria-live="polite" className="min-w-6 text-center font-semibold text-muted-foreground">
                {value}
            </span>
            <Button
                variant="ghost"
                size="icon-sm"
                aria-label="Tambah jumlah"
                disabled={value >= max}
                onClick={() => onChange((current) => Math.min(current + 1, max))}
                className="text-muted-foreground"
            >
                <Plus />
            </Button>
        </div>
    );
}
