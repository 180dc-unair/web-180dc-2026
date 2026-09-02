import { Flame, Star } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { AdminRecord } from '@/types/admin';
import { getPath } from './resource-utils';

type ToggleButtonProps = {
    record: AdminRecord;
    field: string;
    icon: 'star' | 'flame';
    disabled?: boolean;
    onToggle: (record: AdminRecord, field: string) => void;
};

export function ToggleButton({ record, field, icon, disabled, onToggle }: ToggleButtonProps) {
    const Icon = icon === 'star' ? Star : Flame;
    const isActive = Boolean(getPath(record, field));

    return (
        <Button
            variant="ghost"
            size="icon-sm"
            disabled={disabled}
            onClick={() => onToggle(record, field)}
            aria-label={`Toggle ${field}`}
            title={`Toggle ${field}`}
            className={isActive ? 'text-primary' : 'text-black/30'}
        >
            <Icon />
        </Button>
    );
}
