import { useRef } from 'react';
import { Check } from 'lucide-react';
import { LANDING_IMPACT } from '@/contants/public/landing';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function LandingImpact() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} className="bg-accent py-16 lg:py-24">
            <Container className="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div className="flex flex-col gap-8">
                    <h2 data-reveal className="text-3xl font-extrabold text-muted-foreground lg:text-4xl">
                        {LANDING_IMPACT.title}
                    </h2>
                    <ul className="flex flex-col gap-6">
                        {LANDING_IMPACT.items.map((item) => (
                            <li key={item.title} data-reveal className="flex gap-4">
                                <span className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                                    <Check className="size-4" aria-hidden="true" />
                                </span>
                                <div className="flex flex-col gap-1">
                                    <h3 className="font-bold text-muted-foreground">{item.title}</h3>
                                    <p className="text-sm leading-6 text-muted-foreground/85">{item.description}</p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
                <div data-reveal className="rounded-xl border-2 border-primary bg-card p-4 shadow-sm">
                    <img
                        src={LANDING_IMPACT.image}
                        alt={LANDING_IMPACT.imageAlt}
                        loading="lazy"
                        decoding="async"
                        className="w-full rounded-lg object-cover"
                    />
                </div>
            </Container>
        </section>
    );
}