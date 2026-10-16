import { CalendarDays, LayoutGrid, Tag, Users, type LucideIcon } from 'lucide-react';
import type { NavLink } from '@/types/public';
import aboutTeam from '@/assets/landing/about-team.jpg';
import impactPreview from '@/assets/landing/impact-preview.png';

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