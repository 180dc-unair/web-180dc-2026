import { Building2, Globe, Award } from 'lucide-react';
import { useRef } from 'react';
import { ABOUT_ALUMNI } from '@/contants/public/about';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

// TODO: cocokkan ketiga ikon dengan Figma, di screenshot terlihat seperti gedung, globe, dan tag/penghargaan.
const ALUMNI_ICONS = [Building2, Globe, Award];

export function AboutAlumni() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} aria-label={ABOUT_ALUMNI.label} className="bg-accent">
            <Container className="flex flex-col items-center gap-4 py-16 text-center lg:py-24">
                <p data-reveal className="text-7xl font-extrabold tracking-tight text-accent-foreground sm:text-8xl">
                    {ABOUT_ALUMNI.value}
                </p>
                <p data-reveal className="text-base font-semibold text-muted-foreground sm:text-lg">
                    {ABOUT_ALUMNI.label}
                </p>
                <ul data-reveal className="mt-2 flex gap-4 text-primary">
                    {ALUMNI_ICONS.map((Icon, index) => (
                        <li key={index}>
                            <Icon className="size-5" aria-hidden="true" />
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}