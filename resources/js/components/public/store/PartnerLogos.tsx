import { Skeleton } from '@/components/ui/skeleton';
import { PARTNERS_SECTION } from '@/contants/public/store';
import { useClients } from '@/hooks/public/useClients';
import { cn } from '@/lib/utils';
import type { Client } from '@/types/public';
import { Container } from '../layout/Container';

const logoTileClassName = 'flex h-22 w-37 items-center justify-center rounded-md border border-muted-foreground/16 bg-card px-4';

function PartnerLogo({ client }: { client: Client }) {
    const content = client.logo ? (
        <img
            src={client.logo.url}
            alt={client.name}
            loading="lazy"
            decoding="async"
            className="max-h-12 max-w-16 object-contain"
        />
    ) : (
        <span className="text-center text-xs font-semibold text-muted-foreground">{client.name}</span>
    );

    if (!client.website_url) {
        return <div className={logoTileClassName}>{content}</div>;
    }

    return (
        <a
            href={client.website_url}
            target="_blank"
            rel="noopener noreferrer"
            className={cn(logoTileClassName, 'transition-colors hover:border-primary')}
        >
            {content}
        </a>
    );
}

export function PartnerLogos() {
    const clientsQuery = useClients(PARTNERS_SECTION.limit);

    if (clientsQuery.isError || (clientsQuery.isSuccess && clientsQuery.data.length === 0)) {
        return null;
    }

    return (
        <section aria-labelledby="partners-title" className="bg-primary/5">
            <Container className="flex flex-col items-center gap-7 pt-14 pb-16">
                <h2
                    id="partners-title"
                    className="text-center text-xs leading-4.5 font-bold tracking-[1.8px] text-muted-foreground/85 uppercase"
                >
                    {PARTNERS_SECTION.title}
                </h2>
                <ul className="flex flex-wrap justify-center gap-5">
                    {clientsQuery.isPending
                        ? Array.from({ length: PARTNERS_SECTION.limit }, (_, index) => (
                              <li key={index}>
                                  <Skeleton className="h-22 w-37 rounded-md" />
                              </li>
                          ))
                        : clientsQuery.data.map((client) => (
                              <li key={client.id}>
                                  <PartnerLogo client={client} />
                              </li>
                          ))}
                </ul>
                <p className="max-w-160 text-center text-[13px] leading-5.5 text-muted-foreground/85">
                    {PARTNERS_SECTION.description}
                </p>
            </Container>
        </section>
    );
}
