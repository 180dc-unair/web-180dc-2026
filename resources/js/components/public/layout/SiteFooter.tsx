import type { PropsWithChildren } from 'react';
import { Link } from '@inertiajs/react';
import {
    FOOTER_CREDIT,
    FOOTER_LINK_GROUPS,
    FOOTER_TAGLINE,
    NEWSLETTER_TEXT,
    SOCIAL_LINKS,
} from '@/contants/public/navigation';
import { BrandLogo } from './BrandLogo';
import { Container } from './Container';

function FooterHeading({ children }: PropsWithChildren) {
    return <h2 className="text-base font-bold tracking-widest text-primary uppercase">{children}</h2>;
}

export function SiteFooter() {
    const year = new Date().getFullYear();

    return (
        <footer className="border-t border-white/10 bg-muted-foreground text-white">
            <Container className="flex flex-col gap-16 py-20">
                <div className="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="flex flex-col gap-8">
                        <Link href="/" aria-label="Beranda 180DC UNAIR" className="self-start">
                            <BrandLogo />
                        </Link>
                        <p className="leading-relaxed text-white/70">
                            <span className="block">© {year} 180DC UNAIR.</span>
                            {FOOTER_TAGLINE}
                        </p>
                        <ul className="flex gap-4">
                            {SOCIAL_LINKS.map((social) => (
                                <li key={social.label}>
                                    <a
                                        href={social.href}
                                        aria-label={social.label}
                                        className="flex size-11 items-center justify-center rounded-2xl bg-white/10 shadow-xs transition-colors hover:bg-white/20"
                                    >
                                        <img src={social.icon} alt="" />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {FOOTER_LINK_GROUPS.map((group) => (
                        <nav key={group.title} aria-label={group.title} className="flex flex-col gap-6">
                            <FooterHeading>{group.title}</FooterHeading>
                            <ul className="flex flex-col gap-4">
                                {group.links.map((link) => (
                                    <li key={link.label}>
                                        <Link href={link.href} className="transition-colors hover:text-primary">
                                            {link.label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                    ))}

                    <div className="flex flex-col gap-8">
                        <FooterHeading>Newsletter</FooterHeading>
                        <p className="text-white/70">{NEWSLETTER_TEXT}</p>
                    </div>
                </div>

                <p className="border-t border-white/10 pt-12 text-center text-xs font-semibold tracking-widest text-white/40 uppercase">
                    {FOOTER_CREDIT}
                </p>
            </Container>
        </footer>
    );
}
