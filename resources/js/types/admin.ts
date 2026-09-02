import type { LucideIcon } from 'lucide-react';

export type AdminUser = {
    id: string;
    name: string;
    username: string;
    email: string;
    role: string;
};

export type AdminSharedProps = {
    auth: { user: AdminUser | null };
    flash: { success: string | null };
    [key: string]: unknown;
};

export type ApiEnvelope<T> = {
    status: string;
    message: string;
    data: T;
};

export type AdminRecord = Record<string, unknown> & { id: string };

export type MediaAsset = {
    id: string;
    file_id: string;
    url: string;
    created_at: string;
};

export type MediaPurpose =
    | 'articles'
    | 'article-content'
    | 'products'
    | 'services'
    | 'clients'
    | 'general';

export type FieldType =
    | 'text'
    | 'number'
    | 'textarea'
    | 'richtext'
    | 'media'
    | 'password'
    | 'datetime-local'
    | 'boolean'
    | 'select'
    | 'relation';

export type ResourceField = {
    key: string;
    label: string;
    type: FieldType;
    required?: boolean;
    placeholder?: string;
    options?: Array<{ label: string; value: string }>;
    relationUrl?: string;
    relationLabel?: string;
    source?: string;
    previewSource?: string;
    mediaPurpose?: MediaPurpose;
    fullWidth?: boolean;
    optionalOnEdit?: boolean;
};

export type ResourceColumn = {
    key: string;
    label: string;
    type?: 'text' | 'boolean' | 'status' | 'currency' | 'date';
};

export type ResourceConfig = {
    key: string;
    title: string;
    singular: string;
    description: string;
    endpoint: string;
    icon: LucideIcon;
    columns: ResourceColumn[];
    fields: ResourceField[];
    searchKeys: string[];
};
