import gedungUnair from '@/assets/hero/gedung-unair.webp';
import rhombusCluster from '@/assets/hero/rhombus-cluster-store.svg';
import type { FilterOption, ProductType } from '@/types/public';
import { ALL_FILTER_OPTION } from './filters';

export const STORE_HERO = {
    eyebrow: 'Learning Resources',
    title: '180DC Store',
    tagline: 'learn faster, think sharper',
    description:
        'Casebooks, decks, and templates built from real 180DC UNAIR engagements — made to shorten your learning curve for competitions and consulting work.',
    decoration: { src: rhombusCluster, className: '-top-12.5 left-180 h-105 w-155' },
    illustration: { src: gedungUnair, className: 'right-14.5 -bottom-7.25 h-124.75 w-97.5 opacity-55' },
};

export const PRODUCTS_PER_PAGE = 12;

export const PRODUCT_TYPE_LABELS: Record<ProductType, string> = {
    digital: 'Digital',
    physical: 'Physical',
};

export const PRODUCT_TYPE_OPTIONS: FilterOption[] = [
    ALL_FILTER_OPTION,
    { label: PRODUCT_TYPE_LABELS.digital, value: 'digital' },
    { label: PRODUCT_TYPE_LABELS.physical, value: 'physical' },
];

export const PRODUCT_SORT_OPTIONS: FilterOption[] = [
    { label: 'Default', value: 'default' },
    { label: 'Lowest Price', value: 'price-asc' },
    { label: 'Highest Price', value: 'price-desc' },
    { label: 'Best Selling', value: 'best-selling' },
];

export const PARTNERS_SECTION = {
    title: 'Organisasi yang pernah kami dampingi',
    description: 'Proyek pro bono dan kolaborasi bersama organisasi mahasiswa, UMKM, dan social enterprise di Surabaya.',
    limit: 12,
};
