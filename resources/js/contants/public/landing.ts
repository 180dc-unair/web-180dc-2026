import { CalendarDays, LayoutGrid, Search, Tag, Target, TrendingUp, Users, type LucideIcon } from 'lucide-react';
import type { Article, NavLink, Product } from '@/types/public';
import aboutTeam from '@/assets/landing/about-team.jpg';
import impactPreview from '@/assets/landing/impact-preview.png';
import toolkitDashboard from '@/assets/landing/toolkit-impact-dashboard.jpg';
import toolkitDeck from '@/assets/landing/toolkit-consulting-deck.jpg';
import toolkitFinancial from '@/assets/landing/toolkit-financial-model.jpg';
import toolkitBook from '@/assets/landing/toolkit-impact-book.jpg';
import articleFeatured from '@/assets/landing/article-featured.jpg';
import articleImpact from '@/assets/landing/article-impact-measurement.jpg';
import articleEco from '@/assets/landing/article-eco-tourism.jpg';
import articleSystems from '@/assets/landing/article-systems-thinking.jpg';

export const LANDING_HERO = {
    titleLines: ['Accelerating Social', 'Impact Through'],
    titleTail: 'THE',
    badge: '#180WAY',
    description:
        'Empowering social enterprises with student-led consulting excellence. We bridge the gap between academic theory and real-world impact.',
    primaryCta: { label: 'Get Started', href: '/#services' } satisfies NavLink,
    secondaryCta: { label: 'Learn More', href: '/about' } satisfies NavLink,
};

export const LANDING_STATS: { value: string; label: string; icon: LucideIcon }[] = [
    { value: '50+', label: 'Projects', icon: Tag },
    { value: '20+', label: 'Clients', icon: Users },
    { value: '100+', label: 'Members', icon: LayoutGrid },
    { value: '2+', label: 'Years', icon: CalendarDays },
];

export const LANDING_CTA = {
    title: 'Ready to Start Your Journey?',
    description:
        "Whether you're a student looking to make an impact or a social enterprise seeking growth, we're here to help.",
    primaryCta: { label: 'Partner With Us', href: '/contact' } satisfies NavLink,
    secondaryCta: { label: 'Join the Team', href: '/about#team' } satisfies NavLink,
};

export const LANDING_ABOUT = {
    title: 'Leading the Future of Social Consulting',
    description:
        '180 Degrees Consulting UNAIR provides high-quality consulting services to non-profits and social enterprises. Our teams consist of the brightest students trained to tackle complex business challenges with innovative solutions.',
    image: aboutTeam,
    imageAlt: 'Anggota 180DC UNAIR berdiskusi di depan papan tulis',
};

export const LANDING_IMPACT = {
    title: 'Our Impact & Achievements',
    items: [
        {
            title: 'Global Excellence Awards',
            description: 'Recognized as the top-performing branch in Southeast Asia for three consecutive years.',
        },
        {
            title: 'Measurable Social Growth',
            description: 'Helped local enterprises increase operational efficiency by an average of 35%.',
        },
        {
            title: 'Strategic Partnerships',
            description: 'Connected 20+ clients with global funding agencies and corporate CSR programs.',
        },
    ],
    image: impactPreview,
    imageAlt: 'Maximizing impact, driving growth',
};

export const LANDING_SERVICES: {
    title: string;
    items: { title: string; description: string; icon: LucideIcon }[];
} = {
    title: 'Our Consulting Services',
    items: [
        {
            title: 'Operational Growth',
            description: 'Optimizing internal processes and resources for maximum efficiency and reach.',
            icon: TrendingUp,
        },
        {
            title: 'Strategic Planning',
            description: 'Developing long-term roadmaps to scale impact and ensure organizational sustainability.',
            icon: Target,
        },
        {
            title: 'Market Research',
            description: 'Deep-dive analysis into social sectors to uncover opportunities and risks.',
            icon: Search,
        },
    ],
};

const toolkitItem = (slug: string, title: string, shortDescription: string, price: string, image: string): Product => ({
    id: slug,
    title,
    slug,
    type: 'digital',
    status: 'published',
    short_description: shortDescription,
    description: null,
    price,
    stock: 0,
    is_featured: false,
    is_best_seller: false,
    sold_count: 0,
    digital_file_url: null,
    category: null,
    image: { id: slug, url: image },
    created_at: '',
    updated_at: '',
});

export const LANDING_TOOLKIT = {
    title: 'Toolkit & Digital Assets',
    limit: 4,
    fallbackItems: [
        toolkitItem('impact-dashboard', 'Impact Dashboard', 'Strategic measurement tool for NGOs.', '29000', toolkitDashboard),
        toolkitItem('consulting-deck', 'Consulting Deck', 'Premium PowerPoint templates.', '15000', toolkitDeck),
        toolkitItem('financial-model', 'Financial Model', 'Automated Excel impact modeling.', '0', toolkitFinancial),
        toolkitItem('impact-book', 'The Impact Book', 'Guide to social entrepreneurship.', '120000', toolkitBook),
    ],
};

export const LANDING_PARTNERS = {
    badge: 'Trusted By',
    limit: 12,
    fallbackNames: ['Goto', 'Shopee', 'Deloitte', 'PWC', 'KPMG', 'Telkom Indonesia', 'McKinsey'],
};

// Placeholder sampai API artikel berisi data: isi dibuat panjang agar estimasi waktu baca sesuai desain.
const placeholderContent = (minutes: number) => 'word '.repeat(minutes * 200);

const articleItem = (
    slug: string,
    title: string,
    excerpt: string,
    categoryName: string,
    image: string,
    minutes: number,
): Article => ({
    id: slug,
    title,
    slug,
    excerpt,
    content: placeholderContent(minutes),
    status: 'published',
    view_count: 0,
    published_at: null,
    author: null,
    category: { id: categoryName, name: categoryName, slug: categoryName.toLowerCase().replace(/\s+/g, '-') },
    thumbnail: { id: slug, url: image },
    created_at: '',
    updated_at: '',
});

export const LANDING_ARTICLES = {
    title: 'Latest Articles & Perspectives',
    viewAll: { label: 'View All', href: '/articles' } satisfies NavLink,
    sideLimit: 3,
    fallbackItems: [
        articleItem(
            'future-of-social-entrepreneurship',
            'The Future of Social Entrepreneurship in Southeast Asia',
            'How emerging technologies and new funding models are reshaping the landscape for social enterprises in developing nations...',
            'Strategy',
            articleFeatured,
            10,
        ),
        articleItem('impact-measurement-101', 'Impact Measurement 101', '', 'Insights', articleImpact, 5),
        articleItem('revitalizing-local-eco-tourism', 'Revitalizing Local Eco-Tourism', '', 'Case Study', articleEco, 6),
        articleItem('systems-thinking-for-ngos', 'Systems Thinking for NGOs', '', 'Theory', articleSystems, 7),
    ],
};