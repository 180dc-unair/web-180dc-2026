export type FormValue = string | number | boolean | null;
export type FormState = Record<string, FormValue>;

export function getPath(record: Record<string, unknown>, path: string): unknown {
    return path.split('.').reduce<unknown>((value, segment) => {
        if (value && typeof value === 'object' && segment in value) {
            return (value as Record<string, unknown>)[segment];
        }
        return undefined;
    }, record);
}

export function initialForm<T extends { fields: Array<{ key: string; type?: string; source?: string; options?: Array<{ value: string }> }> }>(
    config: T,
    record: Record<string, unknown> | null,
): FormState {
    return Object.fromEntries(config.fields.map((field) => {
        let value = record ? getPath(record, field.source ?? field.key) : undefined;

        if (field.type === 'boolean') value = value ?? false;
        if (field.type === 'select') value = value ?? field.options?.[0]?.value ?? '';
        if (field.type === 'number') value = value ?? '';
        if (field.type === 'datetime-local' && typeof value === 'string') value = value.slice(0, 16);

        return [field.key, (value ?? '') as FormValue];
    }));
}

export function normalizedPayload<T extends { fields: Array<{ key: string; type?: string }> }>(
    config: T,
    form: FormState,
): Record<string, FormValue> {
    return Object.fromEntries(config.fields.map((field) => {
        let value = form[field.key];
        if (field.type === 'number') value = value === '' ? null : Number(value);
        if (field.type === 'relation' && value === '') value = null;
        if (field.type === 'media' && value === '') value = null;
        if (field.type === 'password' && value === '') value = null;
        if (field.type === 'datetime-local' && value === '') value = null;
        return [field.key, value];
    }));
}

export function formatStatus(value: unknown): string {
    return String(value ?? '-')
        .replace(/_/g, ' ')
        .toLowerCase()
        .replace(/\b\w/g, (c: string) => c.toUpperCase());
}

export function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value));
}

export function formatCurrency(value: unknown): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}
