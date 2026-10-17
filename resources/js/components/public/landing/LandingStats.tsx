import { useRef } from 'react';
import { LANDING_STATS } from '@/contants/public/landing';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function LandingStats() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} className="bg-primary">
            <Container className="py-12">
                <ul className="grid grid-cols-2 gap-y-10 lg:grid-cols-4 lg:divide-x lg:divide-white/30">
                    {LANDING_STATS.map(({ value, label, icon: Icon }) => (
                        <li key={label} data-reveal className="flex flex-col items-center gap-3 text-white">
                            <Icon className="size-6" aria-hidden="true" />
                            <p className="text-4xl font-extrabold lg:text-5xl">{value}</p>
                            <p className="text-xs font-semibold tracking-[1.8px] text-white/85 uppercase">{label}</p>
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}