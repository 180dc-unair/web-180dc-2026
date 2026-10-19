import { useRef } from 'react';
import { LANDING_PARTNERS } from '@/contants/public/landing';
import { useClients } from '@/hooks/public/useClients';
import { useMarquee } from '@/hooks/public/useMarquee';
import { Container } from '../layout/Container';

export function LandingPartners() {
    const trackRef = useRef<HTMLUListElement>(null);
    const clientsQuery = useClients(LANDING_PARTNERS.limit);

    const apiNames = (clientsQuery.data ?? []).map((client) => client.name);
    const names = apiNames.length > 0 ? apiNames : LANDING_PARTNERS.fallbackNames;

    useMarquee(trackRef);

    return (
        <section id="partners" aria-label={LANDING_PARTNERS.badge} className="scroll-mt-36 overflow-hidden bg-muted-foreground">
            <Container className="flex flex-col items-center gap-8 py-12">
                <span className="rounded-full bg-primary px-4 py-1 text-xs font-bold tracking-[1.8px] text-white uppercase">
                    {LANDING_PARTNERS.badge}
                </span>
                <div className="w-full overflow-hidden">
                    <ul ref={trackRef} className="flex w-max">
                        {[...names, ...names].map((name, index) => (
                            <li
                                key={`${name}-${index}`}
                                aria-hidden={index >= names.length || undefined}
                                className="px-8 text-2xl font-bold whitespace-nowrap text-primary"
                            >
                                {name}
                            </li>
                        ))}
                    </ul>
                </div>
            </Container>
        </section>
    );
}