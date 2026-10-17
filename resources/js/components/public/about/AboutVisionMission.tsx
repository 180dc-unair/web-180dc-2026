import { useRef } from 'react';
import { ABOUT_MISSION, ABOUT_VALUES, ABOUT_VISION } from '@/contants/public/about';
import { useFloat } from '@/hooks/public/useFloat';
import { useReveal } from '@/hooks/public/useReveal';
import { cn } from '@/lib/utils';
import type { ValuePill } from '@/types/public';
import { Container } from '../layout/Container';

const pillTones: Record<ValuePill['tone'], string> = {
    primary: 'bg-primary text-white',
    secondary: 'bg-secondary-foreground text-white',
    dark: 'bg-muted-foreground text-white',
};

export function AboutVisionMission() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);
    useFloat(sectionRef);

    return (
        <section ref={sectionRef} className="bg-background">
            <Container className="flex flex-col gap-16 py-16 lg:py-24">
                <div className="grid items-start gap-12 lg:grid-cols-2">
                    <div data-reveal className="flex flex-col gap-4 rounded-2xl bg-accent p-8">
                        <h2 className="text-2xl font-bold text-accent-foreground">{ABOUT_VISION.title}</h2>
                        <p className="text-sm leading-6 text-muted-foreground italic">&ldquo;{ABOUT_VISION.quote}&rdquo;</p>
                        <span className="mt-2 h-1 w-12 rounded-full bg-primary" aria-hidden="true" />
                    </div>

                    <div className="flex flex-col gap-6">
                        <h2 data-reveal className="text-2xl font-bold text-muted-foreground">
                            {ABOUT_MISSION.title}
                        </h2>
                        <ol className="flex flex-col gap-6">
                            {ABOUT_MISSION.items.map((item, index) => (
                                <li key={item.title} data-reveal className="flex gap-4">
                                    <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white">
                                        {index + 1}
                                    </span>
                                    <div className="flex flex-col gap-1">
                                        <h3 className="text-base font-bold text-muted-foreground">{item.title}</h3>
                                        <p className="text-sm leading-6 text-muted-foreground/85">{item.description}</p>
                                    </div>
                                </li>
                            ))}
                        </ol>
                    </div>
                </div>

                <ul className="flex flex-wrap justify-center gap-4">
                    {ABOUT_VALUES.map((value, index) => (
                        <li key={value.label} data-reveal className={index % 2 === 0 ? '-rotate-2' : 'rotate-2'}>
                            <div data-float className={cn('rounded-full px-8 py-3 text-sm font-bold shadow-md', pillTones[value.tone])}>
                                {value.label}
                            </div>
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}