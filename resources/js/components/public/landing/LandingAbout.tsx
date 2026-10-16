import { useRef } from 'react';
import { LANDING_ABOUT } from '@/contants/public/landing';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function LandingAbout() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} className="bg-secondary py-16 lg:py-24">
            <Container className="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div data-reveal className="rounded-xl border border-border bg-card p-3 shadow-sm">
                    <img
                        src={LANDING_ABOUT.image}
                        alt={LANDING_ABOUT.imageAlt}
                        loading="lazy"
                        decoding="async"
                        className="aspect-video w-full rounded-lg object-cover shadow-lg"
                    />
                </div>
                <div data-reveal className="flex flex-col gap-5">
                    <h2 className="text-3xl font-extrabold text-muted-foreground lg:text-4xl">{LANDING_ABOUT.title}</h2>
                    <p className="leading-7 text-muted-foreground/85">{LANDING_ABOUT.description}</p>
                </div>
            </Container>
        </section>
    );
}