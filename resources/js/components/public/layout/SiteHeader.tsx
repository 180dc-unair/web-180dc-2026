import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { Button, buttonVariants } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { ANNOUNCEMENT_TEXT, MAIN_NAV_LINKS, SIGN_IN_LINK } from '@/contants/public/navigation';
import { cn } from '@/lib/utils';
import { BrandLogo } from './BrandLogo';
import { Container } from './Container';

const signInClassName = cn(
    buttonVariants(),
    'h-11 rounded-full px-8 text-base font-semibold text-white shadow-xs hover:bg-primary/90',
);

function isActivePath(currentUrl: string, href: string) {
    const currentPath = currentUrl.split(/[?#]/)[0];

    if (href.includes('#')) {
        return false;
    }

    return href === '/' ? currentPath === '/' : currentPath.startsWith(href);
}

function NavLinks({ currentUrl, onNavigate, className }: { currentUrl: string; onNavigate?: () => void; className?: string }) {
    return (
        <ul className={className}>
            {MAIN_NAV_LINKS.map((link) => {
                const active = isActivePath(currentUrl, link.href);

                return (
                    <li key={link.href}>
                        <Link
                            href={link.href}
                            onClick={onNavigate}
                            aria-current={active ? 'page' : undefined}
                            className={cn(
                                'inline-block border-b-2 pb-1.5 text-base transition-colors',
                                active
                                    ? 'border-primary font-bold text-primary'
                                    : 'border-transparent text-muted-foreground/85 hover:text-primary',
                            )}
                        >
                            {link.label}
                        </Link>
                    </li>
                );
            })}
        </ul>
    );
}

export function SiteHeader() {
    const { url } = usePage();
    const [menuOpen, setMenuOpen] = useState(false);
    const closeMenu = () => setMenuOpen(false);

    return (
        <header className="sticky top-0 z-40">
            <div className="border-b border-border bg-background/90 shadow-xs backdrop-blur-sm">
                <Container className="flex items-center justify-between py-4">
                    <Link href="/" aria-label="Beranda 180DC UNAIR">
                        <BrandLogo />
                    </Link>

                    <nav aria-label="Navigasi utama" className="hidden lg:block">
                        <NavLinks currentUrl={url} className="flex items-center gap-12 pt-2" />
                    </nav>

                    <Link href={SIGN_IN_LINK.href} className={cn(signInClassName, 'hidden lg:inline-flex')}>
                        {SIGN_IN_LINK.label}
                    </Link>

                    <Sheet open={menuOpen} onOpenChange={setMenuOpen}>
                        <SheetTrigger
                            render={<Button variant="ghost" size="icon-lg" className="lg:hidden" aria-label="Buka menu" />}
                        >
                            <Menu className="size-6 text-muted-foreground" />
                        </SheetTrigger>
                        <SheetContent side="right" className="w-[min(88vw,20rem)] gap-0 p-0">
                            <SheetHeader className="border-b border-border px-6 py-5">
                                <SheetTitle>
                                    <Link href="/" onClick={closeMenu} aria-label="Beranda 180DC UNAIR">
                                        <BrandLogo />
                                    </Link>
                                </SheetTitle>
                                <SheetDescription className="sr-only">Navigasi utama</SheetDescription>
                            </SheetHeader>
                            <nav aria-label="Navigasi utama" className="flex flex-col gap-8 px-6 py-6">
                                <NavLinks currentUrl={url} onNavigate={closeMenu} className="flex flex-col gap-4" />
                                <Link href={SIGN_IN_LINK.href} onClick={closeMenu} className={signInClassName}>
                                    {SIGN_IN_LINK.label}
                                </Link>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </Container>
            </div>

            <p className="bg-primary/90 px-4 py-2 text-center text-sm text-white sm:text-base">{ANNOUNCEMENT_TEXT}</p>
        </header>
    );
}
