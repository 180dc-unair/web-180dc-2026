import type { PropsWithChildren } from 'react';
import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    BookOpenText,
    Boxes,
    BriefcaseBusiness,
    CalendarDays,
    ChevronRight,
    CircleGauge,
    FolderKanban,
    FolderTree,
    Handshake,
    LogOut,
    Menu,
    MessageSquareText,
    Package,
    PanelLeftClose,
    PanelLeftOpen,
    Tags,
    Users,
    UsersRound,
} from 'lucide-react';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import type { AdminSharedProps } from '@/types/admin';

type AdminLayoutProps = PropsWithChildren<{
    title: string;
    description?: string;
}>;

const navigation = [
    { label: 'Dashboard', href: '/admin', icon: CircleGauge, exact: true },
    { label: 'Produk', href: '/admin/products', icon: Package },
    { label: 'Kategori Produk', href: '/admin/product-categories', icon: Boxes },
    { label: 'Layanan', href: '/admin/services', icon: BriefcaseBusiness },
    { label: 'Kategori Layanan', href: '/admin/service-categories', icon: FolderTree },
    { label: 'Klien & Partner', href: '/admin/clients', icon: Handshake },
    { label: 'Artikel', href: '/admin/articles', icon: BookOpenText },
    { label: 'Kategori Artikel', href: '/admin/article-categories', icon: Tags },
    { label: 'Tim', href: '/admin/team-members', icon: UsersRound },
    { label: 'Kategori Event', href: '/admin/event-categories', icon: FolderKanban },
    { label: 'Event', href: '/admin/events', icon: CalendarDays },
    { label: 'Komentar', href: '/admin/comments', icon: MessageSquareText },
    { label: 'Pengguna', href: '/admin/users', icon: Users },
];

function Brand({ collapsed = false, onNavigate }: { collapsed?: boolean; onNavigate?: () => void }) {
    return (
        <Link href="/admin" className={cn('flex items-center gap-3', collapsed && 'justify-center')} aria-label="180DC Admin" onClick={onNavigate}>
            <span className="grid size-10 place-items-center rounded-xl bg-primary font-extrabold text-primary-foreground shadow-sm">
                180
            </span>
            <span className={cn(collapsed && 'hidden')}>
                <span className="block text-sm font-bold tracking-tight text-black">180DC UNAIR</span>
                <span className="block text-xs text-black/60">Admin Workspace</span>
            </span>
        </Link>
    );
}

function Navigation({ currentUrl, collapsed = false, onNavigate }: { currentUrl: string; collapsed?: boolean; onNavigate?: () => void }) {
    return (
        <nav className="space-y-1" aria-label="Navigasi admin">
            {navigation.map((item) => {
                const active = item.exact ? currentUrl === item.href : currentUrl.startsWith(item.href);
                const Icon = item.icon;

                return (
                    <Link
                        key={item.href}
                        href={item.href}
                        onClick={onNavigate}
                        title={collapsed ? item.label : undefined}
                        aria-label={collapsed ? item.label : undefined}
                        className={cn(
                            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors',
                            collapsed && 'justify-center px-2',
                            active
                                ? 'bg-sidebar-primary text-sidebar-primary-foreground shadow-sm'
                                : 'text-black hover:bg-black/5 hover:text-black',
                        )}
                    >
                        <Icon className="size-4" aria-hidden="true" />
                        <span className={cn('flex-1', collapsed && 'hidden')}>{item.label}</span>
                        {active && !collapsed && <ChevronRight className="size-3.5" aria-hidden="true" />}
                    </Link>
                );
            })}
        </nav>
    );
}

export function AdminLayout({ title, description, children }: AdminLayoutProps) {
    const page = usePage<AdminSharedProps>();
    const user = page.props.auth.user;
    const [collapsed, setCollapsed] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const initials = (user?.name ?? 'Admin')
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();

    const logout = () => router.post('/admin/logout');

    return (
        <div className="min-h-screen bg-muted/35 text-black">
            <aside className={cn('fixed inset-y-0 left-0 z-30 hidden border-r bg-sidebar transition-[width] duration-200 lg:flex lg:flex-col', collapsed ? 'w-20' : 'w-72')}>
                <div className={cn('py-6', collapsed ? 'px-3' : 'px-6')}><Brand collapsed={collapsed} /></div>
                <Separator />
                <div className={cn('flex-1 overflow-y-auto py-5', collapsed ? 'px-2' : 'px-4')}>
                    <Navigation currentUrl={page.url} collapsed={collapsed} />
                </div>
                <div className={cn('border-t', collapsed ? 'p-2' : 'p-4')}>
                    <div className={cn('flex items-center gap-3 rounded-xl bg-background/70 p-3', collapsed && 'flex-col px-2')}>
                        <Avatar className="size-9">
                            <AvatarFallback className="bg-secondary text-black">{initials}</AvatarFallback>
                        </Avatar>
                        <div className={cn('min-w-0 flex-1', collapsed && 'hidden')}>
                            <p className="truncate text-sm font-semibold text-black">{user?.name ?? 'Administrator'}</p>
                            <p className="truncate text-xs text-black/60">{user?.email}</p>
                        </div>
                        <Button variant="ghost" size="icon-sm" onClick={logout} aria-label="Keluar">
                            <LogOut />
                        </Button>
                    </div>
                </div>
            </aside>

            <div className={cn('transition-[padding] duration-200', collapsed ? 'lg:pl-20' : 'lg:pl-72')}>
                <header className="sticky top-0 z-20 border-b bg-background/90 backdrop-blur-md">
                    <div className="flex min-h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                            <SheetTrigger
                                render={<Button variant="outline" size="icon" className="lg:hidden" aria-label="Buka navigasi" />}
                            >
                                <Menu />
                            </SheetTrigger>
                            <SheetContent side="left" className="flex w-[min(88vw,20rem)] flex-col bg-sidebar p-0 text-black">
                                <SheetHeader className="border-b px-6 py-6 text-left">
                                    <SheetTitle><Brand onNavigate={() => setMobileOpen(false)} /></SheetTitle>
                                    <SheetDescription className="sr-only">Navigasi dashboard admin</SheetDescription>
                                </SheetHeader>
                                <div className="min-h-0 flex-1 overflow-y-auto px-4 py-5">
                                    <Navigation currentUrl={page.url} onNavigate={() => setMobileOpen(false)} />
                                </div>
                            </SheetContent>
                        </Sheet>

                        <Button
                            variant="outline"
                            size="icon"
                            className="hidden lg:inline-flex"
                            onClick={() => setCollapsed((value) => !value)}
                            aria-label={collapsed ? 'Buka sidebar' : 'Tutup sidebar'}
                            title={collapsed ? 'Buka sidebar' : 'Tutup sidebar'}
                        >
                            {collapsed ? <PanelLeftOpen /> : <PanelLeftClose />}
                        </Button>

                        <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-semibold text-black">{title}</p>
                            {description && <p className="hidden truncate text-xs text-black/60 sm:block">{description}</p>}
                        </div>
                        <Button variant="outline" size="sm" onClick={logout} className="lg:hidden">
                            <LogOut /> Keluar
                        </Button>
                    </div>
                </header>

                <main className="mx-auto w-full max-w-[1600px] p-4 text-black sm:p-6 lg:p-8 [&_[data-slot=card-description]]:text-black/60 [&_[data-slot=card-title]]:text-black [&_[data-slot=table-cell]]:text-black [&_[data-slot=table-head]]:text-black">
                    {children}
                </main>
            </div>
        </div>
    );
}
