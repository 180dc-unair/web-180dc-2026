import { BRAND_HASHTAGS } from '@/contants/public/brand';
import { cn } from '@/lib/utils';
import type { HeroImage } from '@/types/public';
import { GrainOverlay } from './GrainOverlay';
import { Container } from './layout/Container';

const heroTones = {
    primary: { background: 'bg-primary', tagline: 'text-white/92' },
    secondary: { background: 'bg-foreground', tagline: 'text-primary' },
};

type PageHeroProps = {
    eyebrow: string;
    title: string;
    tagline: string;
    description: string;
    decoration: HeroImage;
    illustration: HeroImage;
    tone?: keyof typeof heroTones;
};

function HeroDecoration({ image }: { image: HeroImage }) {
    return (
        <img
            src={image.src}
            alt=""
            loading="lazy"
            decoding="async"
            className={cn('pointer-events-none absolute hidden max-w-none lg:block', image.className)}
        />
    );
}

export function PageHero({ eyebrow, title, tagline, description, decoration, illustration, tone = 'primary' }: PageHeroProps) {
    return (
        <section className={cn('relative overflow-hidden', heroTones[tone].background)}>
            <GrainOverlay />
            <Container className="relative py-16 lg:py-22">
                <HeroDecoration image={decoration} />
                <HeroDecoration image={illustration} />

                <div className="relative flex max-w-165 flex-col items-start gap-4.5 text-white">
                    <span className="rounded border-[1.5px] border-white/60 px-4 py-1.75 text-xs leading-4 font-bold tracking-[2px] uppercase">
                        {eyebrow}
                    </span>
                    <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-[56px] lg:leading-16">{title}</h1>
                    <p className={cn('font-accent text-2xl leading-10 font-bold sm:text-[34px]', heroTones[tone].tagline)}>
                        {tagline}
                    </p>
                    <p className="text-base leading-7 font-medium text-white/88 sm:text-[17px]">{description}</p>
                    <p className="flex gap-4 text-xs leading-4.5 font-semibold tracking-[1.5px] text-white/65">
                        {BRAND_HASHTAGS.map((hashtag) => (
                            <span key={hashtag}>{hashtag}</span>
                        ))}
                    </p>
                </div>
            </Container>
        </section>
    );
}
