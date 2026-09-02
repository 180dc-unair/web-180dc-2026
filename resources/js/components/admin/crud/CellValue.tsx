import { formatCurrency, formatDate, formatStatus } from './resource-utils';

type CellValueProps = {
    value: unknown;
    type?: string;
};

export function CellValue({ value, type }: CellValueProps) {
    if (type === 'boolean') {
        return <span className="font-medium text-black">{value ? 'Ya' : 'Tidak'}</span>;
    }

    if (type === 'status') {
        return <span className="font-medium text-black">{formatStatus(value)}</span>;
    }

    if (type === 'currency') {
        return <span className="font-medium">{formatCurrency(value)}</span>;
    }

    if (type === 'date') {
        return value
            ? <span className="whitespace-nowrap text-black/55">{formatDate(String(value))}</span>
            : <span className="text-black/55">—</span>;
    }

    return <span className="line-clamp-2 max-w-xs">{String(value ?? '—')}</span>;
}
