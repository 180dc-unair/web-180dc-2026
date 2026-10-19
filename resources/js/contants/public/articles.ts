import garudaAmerta from '@/assets/hero/garuda-amerta.webp';
import rhombusCluster from '@/assets/hero/rhombus-cluster-articles.svg';
import type { FilterOption, Shard } from '@/types/public';

export const ARTICLES_HERO = {
    eyebrow: 'Publication',
    title: 'Our Articles',
    tagline: 'sharpening the #180Way',
    description: 'Frameworks, case notes, and perspectives written by consultants of 180 Degrees Consulting Universitas Airlangga.',
    decoration: { src: rhombusCluster, className: '-top-13 left-180 h-108 w-150' },
    illustration: {
        src: garudaAmerta,
        className: 'top-[calc(50%-31px)] right-5 h-125.5 w-105 -translate-y-1/2 object-contain opacity-70',
    },
};

export const ARTICLES_PER_PAGE = 6;

export const ARTICLE_SORT_OPTIONS: FilterOption[] = [
    { label: 'Newest', value: 'newest' },
    { label: 'Oldest', value: 'oldest' },
    { label: 'Most Viewed', value: 'popular' },
];

export const RELATED_ARTICLES_LIMIT = 3;

export const AUTHOR_ORGANIZATION = '180DC UNAIR';

export const ARTICLE_HERO_SHARDS: Shard[] = [
    { x: '81.67%', y: '12.57%', width: 120, height: 130, opacity: 0.22 },
    { x: '92.56%', y: '24.19%', width: 70, height: 76, opacity: 0.22 },
    { x: '76.6%', y: '28.03%', width: 26, height: 28, opacity: 1 },
    { x: '88.11%', y: '47.67%', width: 54, height: 58, opacity: 0.18 },
];

export const AUTHOR_BANNER_SHARDS: Shard[] = [
    { x: '80.3%', y: '33.8%', width: 90, height: 98, opacity: 0.2 },
    { x: '89.45%', y: '59.8%', width: 54, height: 58, opacity: 0.2 },
    { x: '96.26%', y: '-1.5%', width: 24, height: 26, opacity: 1 },
    { x: '86.24%', y: '109.8%', width: 44, height: 48, opacity: 0.18 },
];
