import { useRef } from 'react';
import { ABOUT_JOURNEY, ABOUT_JOURNEY_SECTION } from '@/contants/public/about';
import { useReveal } from '@/hooks/public/useReveal';
import { cn } from '@/lib/utils';
import { Container } from '../layout/Container';

// Warna selang-seling: entri 1 dan 3 hijau, entri 2 biru (titik dan tahun satu warna).
const journeyTones = [
    { dot: 'bg-primary', year: 'text-primary' },
    { dot: 'bg-secondary-foreground', year: 'text-secondary-foreground' },
];

export function AboutJourney() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} aria-labelledby="about-journey-title" className="bg-background">
            <Container className="flex flex-col gap-14 py-16 lg:py-24">
                <h2
                    id="about-journey-title"
                    data-reveal
                    className="text-center text-3xl font-extrabold text-muted-foreground sm:text-4xl"
                >
                    {ABOUT_JOURNEY_SECTION.title}
                </h2>

                <ol className="relative mx-auto flex w-full max-w-4xl flex-col gap-10">
                    <span
                        aria-hidden="true"
                        className="absolute top-2 bottom-2 left-4 w-0.5 bg-secondary-foreground/40 lg:left-1/2 lg:-translate-x-1/2"
                    />

                    {ABOUT_JOURNEY.map((entry, index) => {
                        const cardOnLeft = index % 2 === 0;
                        const tone = journeyTones[index % journeyTones.length];

                        return (
                            <li
                                key={entry.year}
                                data-reveal
                                className="relative grid gap-3 pl-12 lg:grid-cols-2 lg:items-center lg:gap-20 lg:pl-0"
                            >
                                <span
                                    aria-hidden="true"
                                    className={cn(
                                        'absolute top-3 left-4 size-4 -translate-x-1/2 rounded-full border-4 border-background lg:top-1/2 lg:left-1/2 lg:-translate-y-1/2',
                                        tone.dot,
                                    )}
                                />

                                <p
                                    className={cn(
                                        'text-4xl font-extrabold lg:row-start-1 lg:text-6xl',
                                        tone.year,
                                        cardOnLeft ? 'lg:col-start-2' : 'lg:col-start-1 lg:text-right',
                                    )}
                                >
                                    {entry.year}
                                </p>

                                <div
                                    className={cn(
                                        'relative flex flex-col gap-2 rounded-xl bg-card p-6 shadow-md lg:row-start-1',
                                        cardOnLeft ? 'lg:col-start-1' : 'lg:col-start-2',
                                    )}
                                >
                                    {entry.badge && (
                                        <span className="absolute -top-3 left-6 rounded-full bg-primary px-3 py-0.5 text-[11px] font-bold text-white">
                                            {entry.badge}
                                        </span>
                                    )}
                                    <h3 className="text-base font-bold text-muted-foreground">{entry.title}</h3>
                                    <p className="text-sm leading-6 text-muted-foreground/85">{entry.description}</p>
                                </div>
                            </li>
                        );
                    })}
                </ol>
            </Container>
        </section>
    );
}