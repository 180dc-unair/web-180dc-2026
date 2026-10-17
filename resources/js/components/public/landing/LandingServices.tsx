import { useRef } from 'react';
import { LANDING_SERVICES } from '@/contants/public/landing';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function LandingServices() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} id="services" className="scroll-mt-36 bg-muted-foreground py-16 lg:py-24">
            <Container className="flex flex-col gap-12">
                <h2 data-reveal className="text-center text-3xl font-extrabold text-white lg:text-4xl">
                    {LANDING_SERVICES.title}
                </h2>
                <ul className="grid gap-6 md:grid-cols-3">
                    {LANDING_SERVICES.items.map(({ title, description, icon: Icon }) => (
                        <li key={title} data-reveal className="flex flex-col gap-4 rounded-lg bg-primary p-8 text-white">
                            <span className="flex size-12 items-center justify-center rounded-full bg-white/25">
                                <Icon className="size-5" aria-hidden="true" />
                            </span>
                            <h3 className="text-xl font-bold">{title}</h3>
                            <p className="text-sm leading-6 text-white/90">{description}</p>
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}