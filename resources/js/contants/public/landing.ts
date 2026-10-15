import { CalendarDays, LayoutGrid, Tag, Users, type LucideIcon } from 'lucide-react';
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

export const LANDING_STATS: { value: string; label: string; icon: LucideIcon }[] = [
    { value: '50+', label: 'Projects', icon: Tag },
    { value: '20+', label: 'Clients', icon: Users },
    { value: '100+', label: 'Members', icon: LayoutGrid },
    { value: '2+', label: 'Years', icon: CalendarDays },
];