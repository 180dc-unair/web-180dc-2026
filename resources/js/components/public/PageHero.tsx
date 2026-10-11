import { BRAND_HASHTAGS } from '@/contants/public/brand';
import { cn } from '@/lib/utils';
import { GrainOverlay } from './GrainOverlay';
import { Container } from './layout/Container';

type PageHeroProps = {
    eyebrow: string;
    title: string;
    tagline: string;
    description: string;
    decoration: string;
    illustration: string;
    illustrationClassName?: string;
};

export function PageHero({ eyebrow, title, tagline, description, decoration, illustration, illustrationClassName }: PageHeroProps) {
    return (
        <section className="relative overflow-hidden bg-primary">
            <GrainOverlay />
            <Container className="relative py-16 lg:py-22">
                <img
                    src={decoration}
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className="pointer-events-none absolute -top-13 left-180 hidden h-108 w-150 max-w-none lg:block"
                />
                <img
                    src={illustration}
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className={cn(
                        'pointer-events-none absolute right-5 hidden object-contain opacity-70 lg:block',
                        illustrationClassName,
                    )}
                />

                <div className="relative flex max-w-165 flex-col items-start gap-4.5 text-white">
                    <span className="rounded border-[1.5px] border-white/60 px-4 py-1.75 text-xs leading-4 font-bold tracking-[2px] uppercase">
                        {eyebrow}
                    </span>
                    <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-[56px] lg:leading-16">{title}</h1>
                    <p className="font-accent text-2xl leading-10 font-bold text-white/92 sm:text-[34px]">{tagline}</p>
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
