import type { JourneyEntry, MissionItem, ValuePill } from '@/types/public';

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

// TODO: cocokkan teks pill dengan Figma, dan tambahkan pill kelima yang belum terbaca dari screenshot.
export const ABOUT_VALUES: ValuePill[] = [
    { label: 'One Vision', tone: 'primary' },
    { label: 'Infinite Growth', tone: 'secondary' },
    { label: 'Radical Honesty', tone: 'primary' },
    { label: 'Social Heart', tone: 'dark' },
    { label: 'Innovative Solutions', tone: 'secondary' },
];

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