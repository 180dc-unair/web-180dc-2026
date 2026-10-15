import type { NavLink } from '@/types/public';

export const LANDING_HERO = {
    titleLines: ['Accelerating Social', 'Impact Through'],
    titleTail: 'THE',
    badge: '#180WAY',
    description:
        'Empowering social enterprises with student-led consulting excellence. We bridge the gap between academic theory and real-world impact.',
    primaryCta: { label: 'Get Started', href: '/#services' } satisfies NavLink,
    secondaryCta: { label: 'Learn More', href: '/about' } satisfies NavLink,
};