import deloitteLogo from '@/assets/about/partners/deloitte.png';
import eyLogo from '@/assets/about/partners/ey.png';
import kpmgLogo from '@/assets/about/partners/kpmg.png';
import mckinseyLogo from '@/assets/about/partners/mckinsey.png';
import pwcLogo from '@/assets/about/partners/pwc.png';
import shopeeLogo from '@/assets/about/partners/shopee.png';
import telkomLogo from '@/assets/about/partners/telkom.png';
import { ALL_FILTER_OPTION } from '@/contants/public/filters';
import type { Client, FilterOption, JourneyEntry, MissionItem, ValuePill } from '@/types/public';

export const ABOUT_HERO = {
    eyebrow: 'About Our Branch',
    title: 'Who Are We?',
    description:
        '180 Degrees Consulting Universitas Airlangga is part of the world’s largest volunteer consultancy for non-profits and social enterprises. We bridge the gap between high-potential university students and organizations that need professional guidance.',
    scrollLabel: 'Scroll to discover',
};

export const ABOUT_VISION = {
    title: 'Our Vision',
    quote: 'To empower the next generation of social leaders and maximize the impact of socially-conscious organizations through innovative consulting excellence.',
};

export const ABOUT_MISSION: { title: string; items: MissionItem[] } = {
    title: 'Our Mission',
    items: [
        {
            title: 'Excellence in Service',
            description: 'Providing high-quality, data-driven consulting services to social enterprises often underserved by traditional firms.',
        },
        {
            title: 'Student Development',
            description: 'Nurturing talented students through hands-on experience, professional mentorship, and global networking.',
        },
        {
            title: 'Social Transformation',
            description: 'Driving meaningful change in local and global communities by solving complex organizational challenges.',
        },
    ],
};

export const ABOUT_VALUES: ValuePill[] = [
    { label: 'One Vision', tone: 'primary' },
    { label: 'Infinite Growth', tone: 'secondary' },
    { label: 'Radical Honesty', tone: 'primary' },
    { label: 'Social Heart', tone: 'dark' },
    { label: 'Data Driven', tone: 'dark' },
];

export const ABOUT_PARTNERS = {
    title: 'Trusted Partners & Clients',
    limit: 12,
};

export const ABOUT_PARTNER_FILTERS: FilterOption[] = [
    ALL_FILTER_OPTION,
    { label: 'Case Collaboration', value: 'Case Collaboration' },
    { label: 'Strategic Partners', value: 'Strategic Partners' },
    { label: 'NGO Network', value: 'NGO Network' },
];

// TODO: data cadangan sampai API clients berisi. Kategori tiap klien belum diketahui (tanya manajer).
const FALLBACK_PARTNERS: { name: string; type: string; logo: string }[] = [
    { name: 'EY', type: 'Strategic Partners', logo: eyLogo },
    { name: 'Deloitte', type: 'Strategic Partners', logo: deloitteLogo },
    { name: 'KPMG', type: 'Strategic Partners', logo: kpmgLogo },
    { name: 'Shopee', type: 'Case Collaboration', logo: shopeeLogo },
    { name: 'PwC', type: 'Strategic Partners', logo: pwcLogo },
    { name: 'McKinsey & Company', type: 'Strategic Partners', logo: mckinseyLogo },
    { name: 'Telkom Indonesia', type: 'Case Collaboration', logo: telkomLogo },
];

export const ABOUT_PARTNERS_FALLBACK: Client[] = FALLBACK_PARTNERS.map(({ name, type, logo }, index) => ({
    id: `fallback-${index}`,
    name,
    slug: name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
    type,
    logo: { id: `fallback-logo-${index}`, url: logo },
    website_url: null,
    is_featured: false,
    sort_order: index,
}));

export const ABOUT_ALUMNI = {
    value: '150+',
    label: 'Alumni Impacting in Top Companies',
};

export const ABOUT_JOURNEY: JourneyEntry[] = [
    {
        year: '2024',
        title: 'The Inception',
        description: '180DC UNAIR established as the pioneer branch in East Java, starting with 10 visionary members.',
    },
    {
        year: '2025',
        title: 'Expansion Era',
        description: 'Consulted for 15+ local SMEs and reached the Global Branch Finalist nomination.',
    },
    {
        year: '2026',
        title: 'Strategic Dominance',
        description: 'Hosted the first 180DC Business Case Competition and established alumni board.',
        badge: 'Milestone',
    },
];