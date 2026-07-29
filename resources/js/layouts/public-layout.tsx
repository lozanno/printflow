import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
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
        <div className="flex min-h-screen flex-col bg-zinc-50">
            <header className="border-b border-zinc-200 bg-white">
                <div className="mx-auto flex max-w-4xl items-center px-6 py-4">
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
                                                className="text-sm text-zinc-600 hover:text-zinc-900"
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
                                                className="text-sm text-zinc-600 hover:text-zinc-900"
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
                                                className="text-sm text-zinc-600 hover:text-zinc-900"
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
                                                href={footer.contact.whatsapp_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-zinc-600 hover:text-zinc-900"
                                            >
                                                WhatsApp
                                            </a>
                                        </li>
                                    )}
                                    {footer.contact.facebook_url && (
                                        <li>
                                            <a
                                                href={footer.contact.facebook_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-zinc-600 hover:text-zinc-900"
                                            >
                                                Facebook
                                            </a>
                                        </li>
                                    )}
                                    {footer.contact.instagram_url && (
                                        <li>
                                            <a
                                                href={footer.contact.instagram_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-zinc-600 hover:text-zinc-900"
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
