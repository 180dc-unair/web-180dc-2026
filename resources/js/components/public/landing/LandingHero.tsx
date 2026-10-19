import { useRef } from 'react';
import { Link } from '@inertiajs/react';
import { buttonVariants } from '@/components/ui/button';
import { LANDING_HERO } from '@/contants/public/landing';
import { useReveal } from '@/hooks/public/useReveal';
import { cn } from '@/lib/utils';
import { Container } from '../layout/Container';

const primaryCtaClassName = cn(
    buttonVariants(),
    'h-12 rounded-full px-9 text-sm font-semibold text-white shadow-lg shadow-primary/40 hover:bg-primary/90',
);
const secondaryCtaClassName = cn(
    buttonVariants({ variant: 'outline' }),
    'h-12 rounded-full border-white/80 bg-transparent px-9 text-sm font-semibold text-white hover:bg-white/10 hover:text-white',
);

export function LandingHero() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} className="bg-muted-foreground">
            <Container className="py-16 lg:py-28">
                <div className="flex flex-col items-start gap-7 text-white">
                    <h1
                        data-reveal
                        className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-[64px] lg:leading-[1.15]"
                    >
                        {LANDING_HERO.titleLines.map((line) => (
                            <span key={line} className="block">
                                {line}
                            </span>
                        ))}
                        <span className="mt-2 flex flex-wrap items-center gap-4">
                            {LANDING_HERO.titleTail}
                            <span className="-rotate-3 rounded-sm border-2 border-primary px-3 py-0.5 text-2xl font-medium sm:text-3xl lg:text-4xl">
                                {LANDING_HERO.badge}
                            </span>
                        </span>
                    </h1>
                    <p data-reveal className="max-w-2xl text-base leading-7 text-white/85 sm:text-lg">
                        {LANDING_HERO.description}
                    </p>
                    <div data-reveal className="flex flex-wrap gap-4">
                        <Link href={LANDING_HERO.primaryCta.href} className={primaryCtaClassName}>
                            {LANDING_HERO.primaryCta.label}
                        </Link>
                        <Link href={LANDING_HERO.secondaryCta.href} className={secondaryCtaClassName}>
                            {LANDING_HERO.secondaryCta.label}
                        </Link>
                    </div>
                </div>
            </Container>
        </section>
    );
}