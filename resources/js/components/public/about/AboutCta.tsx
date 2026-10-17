import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import { buttonVariants } from '@/components/ui/button';
import { ABOUT_CTA } from '@/contants/public/about';
import { useReveal } from '@/hooks/public/useReveal';
import { cn } from '@/lib/utils';
import { Container } from '../layout/Container';

const ctaClassName = cn(
    buttonVariants(),
    'h-12 rounded-full px-9 text-sm font-semibold text-white shadow-lg shadow-primary/40 hover:bg-primary/90',
);

export function AboutCta() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} aria-labelledby="about-cta-title" className="bg-accent">
            <Container className="flex flex-col items-center gap-6 py-20 text-center lg:py-28">
                <h2
                    id="about-cta-title"
                    data-reveal
                    className="text-4xl font-extrabold tracking-tight text-muted-foreground sm:text-5xl lg:text-6xl"
                >
                    {ABOUT_CTA.titleLead} <span className="text-primary">{ABOUT_CTA.titleHighlight}</span>
                </h2>
                <p data-reveal className="max-w-xl text-base text-secondary-foreground sm:text-lg">
                    {ABOUT_CTA.description}
                </p>
                <div data-reveal>
                    <Link href={ABOUT_CTA.cta.href} className={ctaClassName}>
                        {ABOUT_CTA.cta.label}
                    </Link>
                </div>
            </Container>
        </section>
    );
}