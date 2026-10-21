import { ArrowDown } from 'lucide-react';
import { useRef } from 'react';
import heroCollage from '@/assets/about/hero-collage.png';
import { ABOUT_HERO } from '@/contants/public/about';
import { useReveal } from '@/hooks/public/useReveal';
import { cn } from '@/lib/utils';
import { Container } from '../layout/Container';

// TODO: placeholder, setelah meeting ganti src tiap kartu dengan 3 foto asli dari Figma
const COLLAGE_CARDS = [
    { src: heroCollage, className: 'top-12 left-0 rotate-[-9deg] opacity-80' },
    { src: heroCollage, className: 'top-4 right-0 rotate-[8deg] opacity-80' },
    { src: heroCollage, className: 'top-0 left-1/2 z-10 -translate-x-1/2' },
];

export function AboutHero() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} className="overflow-hidden bg-muted-foreground">
            <Container className="grid items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">
                <div className="flex max-w-xl flex-col items-start gap-5 text-white">
                    <span
                        data-reveal
                        className="rounded-full bg-primary/20 px-4 py-1.5 text-xs font-bold tracking-[2px] text-primary uppercase"
                    >
                        {ABOUT_HERO.eyebrow}
                    </span>
                    <h1 data-reveal className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-[56px] lg:leading-16">
                        {ABOUT_HERO.title}
                    </h1>
                    <p data-reveal className="text-base leading-7 text-white/85">
                        {ABOUT_HERO.description}
                    </p>
                    <p
                        data-reveal
                        className="flex items-center gap-2 text-xs font-semibold tracking-[1.5px] text-primary uppercase"
                    >
                        {ABOUT_HERO.scrollLabel}
                        <ArrowDown className="size-4" aria-hidden="true" />
                    </p>
                </div>

                <div data-reveal className="relative hidden h-[420px] w-[420px] justify-self-center lg:block">
                    {COLLAGE_CARDS.map((card, index) => (
                        <img
                            key={index}
                            src={card.src}
                            alt={index === COLLAGE_CARDS.length - 1 ? 'Foto tim 180DC UNAIR di depan gedung Universitas Airlangga' : ''}
                            aria-hidden={index === COLLAGE_CARDS.length - 1 ? undefined : true}
                            decoding="async"
                            className={cn('absolute h-[360px] w-[270px] object-contain', card.className)}
                        />
                    ))}
                </div>
            </Container>
        </section>
    );
}