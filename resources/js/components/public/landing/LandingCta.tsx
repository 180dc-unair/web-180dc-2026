import { useRef } from 'react';
import { Link } from '@inertiajs/react';
import { buttonVariants } from '@/components/ui/button';
import { LANDING_CTA } from '@/contants/public/landing';
import { useReveal } from '@/hooks/public/useReveal';
import { cn } from '@/lib/utils';
import { Container } from '../layout/Container';

const ctaClassName = cn(
    buttonVariants(),
    'h-12 rounded-full bg-white px-9 text-sm font-semibold text-primary hover:bg-white/90',
);

export function LandingCta() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} className="bg-background py-12 lg:py-16">
            <Container>
                <div className="flex flex-col items-center gap-6 rounded-lg bg-primary px-6 py-14 text-center text-white lg:py-20">
                    <h2 data-reveal className="text-3xl font-extrabold sm:text-4xl lg:text-5xl">
                        {LANDING_CTA.title}
                    </h2>
                    <p data-reveal className="max-w-xl text-base leading-7 text-white/90 sm:text-lg">
                        {LANDING_CTA.description}
                    </p>
                    <div data-reveal className="flex flex-wrap justify-center gap-4">
                        <Link href={LANDING_CTA.primaryCta.href} className={ctaClassName}>
                            {LANDING_CTA.primaryCta.label}
                        </Link>
                        <Link href={LANDING_CTA.secondaryCta.href} className={ctaClassName}>
                            {LANDING_CTA.secondaryCta.label}
                        </Link>
                    </div>
                </div>
            </Container>
        </section>
    );
}