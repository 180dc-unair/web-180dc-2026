import { useRef, useState } from 'react';
import { FilterChips } from '@/components/public/FilterChips';
import { PartnerLogo } from '@/components/public/store/PartnerLogos';
import { Skeleton } from '@/components/ui/skeleton';
import { ABOUT_PARTNER_FILTERS, ABOUT_PARTNERS, ABOUT_PARTNERS_FALLBACK } from '@/contants/public/about';
import { ALL_FILTER_OPTION } from '@/contants/public/filters';
import { useClients } from '@/hooks/public/useClients';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function AboutPartners() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    const [filter, setFilter] = useState(ALL_FILTER_OPTION.value);
    const clientsQuery = useClients(ABOUT_PARTNERS.limit);

    const clients = clientsQuery.isSuccess && clientsQuery.data.length > 0 ? clientsQuery.data : ABOUT_PARTNERS_FALLBACK;
    const visibleClients = filter === ALL_FILTER_OPTION.value ? clients : clients.filter((client) => client.type === filter);

    return (
        <section ref={sectionRef} aria-labelledby="about-partners-title" className="bg-primary">
            <Container className="flex flex-col items-center gap-8 py-16 lg:py-20">
                <h2 id="about-partners-title" data-reveal className="text-center text-2xl font-bold text-white sm:text-3xl">
                    {ABOUT_PARTNERS.title}
                </h2>

                <div data-reveal>
                    <FilterChips
                        label="Partner categories"
                        options={ABOUT_PARTNER_FILTERS}
                        value={filter}
                        onChange={setFilter}
                        tone="onPrimary"
                        className="justify-center"
                    />
                </div>

                <ul data-reveal className="flex flex-wrap justify-center gap-4">
                    {clientsQuery.isPending
                        ? Array.from({ length: 7 }, (_, index) => (
                              <li key={index}>
                                  <Skeleton className="h-22 w-37 rounded-md" />
                              </li>
                          ))
                        : visibleClients.map((client) => (
                              <li key={client.id}>
                                  <PartnerLogo client={client} />
                              </li>
                          ))}
                </ul>
            </Container>
        </section>
    );
}