import { Head, Link } from '@inertiajs/react';
import { home } from '@/routes';

export default function PageShow({
    page,
}: {
    page: { title: string; content: string | null };
}) {
    return (
        <>
            <Head title={page.title} />

            <div className="px-6 py-12">
                <div className="mx-auto max-w-3xl">
                    <Link
                        href={home()}
                        className="text-sm text-zinc-500 hover:text-zinc-700"
                    >
                        &larr; Volver al catalogo
                    </Link>

                    <h1 className="mt-4 text-4xl font-bold tracking-tight text-zinc-900">
                        {page.title}
                    </h1>

                    {page.content && (
                        <div
                            className="prose prose-zinc mt-8 max-w-none"
                            // page.content is sanitized server-side (see
                            // App\Models\Page::content()) before it's ever
                            // stored, so this is safe to render as-is.
                            dangerouslySetInnerHTML={{
                                __html: page.content,
                            }}
                        />
                    )}
                </div>
            </div>
        </>
    );
}
