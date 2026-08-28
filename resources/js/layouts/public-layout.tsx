import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { home } from '@/routes';
import { show } from '@/routes/catalog';

export default function PublicLayout({ children }: { children: ReactNode }) {
    const { shop, footer } = usePage().props;
    const hasContact =
        footer &&
        (footer.contact.email ||
            footer.contact.phone ||
            footer.contact.facebook_url ||
            footer.contact.instagram_url ||
            footer.contact.whatsapp_url);

    return (
        <div
            className="flex min-h-screen flex-col bg-zinc-50"
            style={
                {
                    '--shop-primary': shop?.brand_color || '#18181b',
                    '--shop-accent': shop?.accent_color || '#18181b',
                } as React.CSSProperties
            }
        >
            <header className="border-b border-zinc-200 bg-white">
                <div className="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 text-lg font-bold tracking-tight text-zinc-900"
                    >
                        {shop?.logo_url ? (
                            <img
                                src={shop.logo_url}
                                alt={shop.name}
                                className="h-8 w-auto"
                            />
                        ) : (
                            shop?.name
                        )}
                    </Link>

                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label="Abrir menu"
                            >
                                <Menu className="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right">
                            <SheetHeader>
                                <SheetTitle>{shop?.name ?? 'Menu'}</SheetTitle>
                            </SheetHeader>
                            <nav className="flex flex-col gap-1 overflow-y-auto px-4 pb-4">
                                {footer && footer.categories.length > 0 && (
                                    <>
                                        <p className="mt-2 px-3 text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                                            Categorias
                                        </p>
                                        {footer.categories.map((category) => (
                                            <SheetClose
                                                asChild
                                                key={category.slug}
                                            >
                                                <Link
                                                    href={show(category.slug)}
                                                    className="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                                                >
                                                    {category.title}
                                                </Link>
                                            </SheetClose>
                                        ))}
                                    </>
                                )}

                                {footer && footer.pages.length > 0 && (
                                    <>
                                        <p className="mt-4 px-3 text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                                            Paginas
                                        </p>
                                        {footer.pages.map((page) => (
                                            <SheetClose asChild key={page.slug}>
                                                <Link
                                                    href={show(page.slug)}
                                                    className="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                                                >
                                                    {page.title}
                                                </Link>
                                            </SheetClose>
                                        ))}
                                    </>
                                )}
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            {footer && (
                <footer className="border-t border-zinc-200 bg-white">
                    <div className="mx-auto grid max-w-4xl gap-8 px-6 py-10 sm:grid-cols-3">
                        {footer.categories.length > 0 && (
                            <div>
                                <h3 className="text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                                    Categorias
                                </h3>
                                <ul className="mt-3 space-y-2">
                                    {footer.categories.map((category) => (
                                        <li key={category.slug}>
                                            <Link
                                                href={show(category.slug)}
                                                className="text-sm text-zinc-600 hover:text-[var(--shop-accent)]"
                                            >
                                                {category.title}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {footer.pages.length > 0 && (
                            <div>
                                <h3 className="text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                                    Paginas
                                </h3>
                                <ul className="mt-3 space-y-2">
                                    {footer.pages.map((page) => (
                                        <li key={page.slug}>
                                            <Link
                                                href={show(page.slug)}
                                                className="text-sm text-zinc-600 hover:text-[var(--shop-accent)]"
                                            >
                                                {page.title}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {hasContact && (
                            <div>
                                <h3 className="text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                                    Contacto
                                </h3>
                                <ul className="mt-3 space-y-2">
                                    {footer.contact.email && (
                                        <li>
                                            <a
                                                href={`mailto:${footer.contact.email}`}
                                                className="text-sm text-zinc-600 hover:text-[var(--shop-accent)]"
                                            >
                                                {footer.contact.email}
                                            </a>
                                        </li>
                                    )}
                                    {footer.contact.phone && (
                                        <li className="text-sm text-zinc-600">
                                            {footer.contact.phone}
                                        </li>
                                    )}
                                    {footer.contact.whatsapp_url && (
                                        <li>
                                            <a
                                                href={
                                                    footer.contact.whatsapp_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-zinc-600 hover:text-[var(--shop-accent)]"
                                            >
                                                WhatsApp
                                            </a>
                                        </li>
                                    )}
                                    {footer.contact.facebook_url && (
                                        <li>
                                            <a
                                                href={
                                                    footer.contact.facebook_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-zinc-600 hover:text-[var(--shop-accent)]"
                                            >
                                                Facebook
                                            </a>
                                        </li>
                                    )}
                                    {footer.contact.instagram_url && (
                                        <li>
                                            <a
                                                href={
                                                    footer.contact.instagram_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-zinc-600 hover:text-[var(--shop-accent)]"
                                            >
                                                Instagram
                                            </a>
                                        </li>
                                    )}
                                </ul>
                            </div>
                        )}
                    </div>

                    {shop && (
                        <div className="border-t border-zinc-100 px-6 py-4 text-center text-xs text-zinc-400">
                            &copy; {new Date().getFullYear()} {shop.name}
                        </div>
                    )}
                </footer>
            )}
        </div>
    );
}
