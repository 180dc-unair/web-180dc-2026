import deloitteLogo from '@/assets/about/partners/deloitte.png';
import eyLogo from '@/assets/about/partners/ey.png';
import kpmgLogo from '@/assets/about/partners/kpmg.png';
import mckinseyLogo from '@/assets/about/partners/mckinsey.png';
import pwcLogo from '@/assets/about/partners/pwc.png';
import shopeeLogo from '@/assets/about/partners/shopee.png';
import telkomLogo from '@/assets/about/partners/telkom.png';
import teamPlaceholder from '@/assets/about/team-placeholder.jpg';
import { ALL_FILTER_OPTION } from '@/contants/public/filters';
import type { Client, FilterOption, JourneyEntry, LeadershipMember, MissionItem, ValuePill } from '@/types/public';

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

export const ABOUT_JOURNEY_SECTION = {
    title: 'Our Journey',
};

const slugify = (value: string) => value.toLowerCase().replace(/[^a-z0-9]+/g, '-');

// Foto sementara (satu foto untuk semua anggota) sampai foto asli dari API tersedia.
// TODO: tabel team_members belum punya kolom jabatan (position), tanyakan ke manajer/backend.
const leadershipRow = (...entries: [name: string, position: string][]): LeadershipMember[] =>
    entries.map(([name, position]) => ({
        id: slugify(name),
        name,
        position,
        photo: teamPlaceholder,
    }));

export const ABOUT_LEADERSHIP = {
    title: 'Leadership Team',
    description: 'The minds driving impact at 180DC UNAIR.',
    rows: [
        leadershipRow(
            ['Muhammad Dafi Aritama', 'External Vice President'],
            ['Amabel Maritza Maudina', 'Branch President'],
            ['Farrelindra Nadhif Islami F', 'Internal Vice President'],
        ),
        leadershipRow(
            ['Delfin Erwi Anggara', 'Director of Consulting'],
            ['Christiana I. Larasati Rubino', 'Co-Director of Consulting'],
            ['Gerard Viriyandra', 'Director of Client Engagement'],
            ['Ahmad Hasan Fataihi', 'Director of Marketing Communication'],
        ),
        leadershipRow(
            ['M. Noermario Valentino', 'Co-Director of Marketing Communication'],
            ['M. Rafif Fawwaz Akilah', 'Manager of Strategic Marketing'],
            ['Muhammad Ra’uf Alfarizi', 'Manager of Brand Motion'],
            ['Alya Shafiqa Abdullah', 'Management of Brand Design'],
        ),
        leadershipRow(
            ['Misbahul Muttaqin', 'Manager of Information Technology'],
            ['Olyvia Nthania Sundoro', 'Manager of Partnership'],
            ['Ryan Arsito Pahlevi', 'Director of Strategy & Growth'],
            ['Jessica Rachel Enoch', 'Manager of Human Resources'],
        ),
        leadershipRow(
            ['Natasya Alifia Putri', 'Director of Talent Management'],
            ['Nelson Rachel Rafael', 'Director of Finance & Legal'],
            ['Muhammad Rifki Ardiansyah', 'Legal Manager'],
        ),
    ],
};

export const ABOUT_CTA = {
    titleLead: 'Become Part of',
    titleHighlight: 'The Legacy',
    description: 'Every story of impact starts with a single decision to act.',
    cta: {
        label: 'Join the Team',
        // TODO: samakan dengan tujuan tombol "Join the Team" di landing page. Sementara '#'.
        href: '#',
    },
};