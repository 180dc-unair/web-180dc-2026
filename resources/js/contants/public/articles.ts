import type { FilterOption } from '@/types/public';

export const ARTICLES_HERO = {
    eyebrow: 'Publication',
    title: 'Our Articles',
    tagline: 'sharpening the #180Way',
    description: 'Frameworks, case notes, and perspectives written by consultants of 180 Degrees Consulting Universitas Airlangga.',
};

export const ARTICLES_PER_PAGE = 6;

export const ALL_CATEGORIES_OPTION: FilterOption = { label: 'All', value: 'all' };

export const ARTICLE_SORT_OPTIONS: FilterOption[] = [
    { label: 'Newest', value: 'newest' },
    { label: 'Oldest', value: 'oldest' },
    { label: 'Most Viewed', value: 'popular' },
];
