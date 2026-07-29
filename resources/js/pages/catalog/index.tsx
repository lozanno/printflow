import { Head, Link } from '@inertiajs/react';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { show } from '@/routes/catalog';

type CatalogProductSummary = {
    id: number;
    name: string;
};

export default function CatalogIndex({
    shopName,
    catalogProducts,
}: {
    shopName: string;
    catalogProducts: CatalogProductSummary[];
}) {
    return (
        <>
            <Head title={shopName} />

            <div className="min-h-screen bg-zinc-50 px-6 py-12">
                <div className="mx-auto max-w-4xl">
                    <p className="text-sm font-semibold tracking-wide text-zinc-500 uppercase">
                        {shopName}
                    </p>
                    <h1 className="mt-1 text-4xl font-bold tracking-tight text-zinc-900">
                        ¿Que deseas imprimir?
                    </h1>
                    <p className="mt-2 text-zinc-600">
                        Elige un producto y cotizalo en menos de 30 segundos.
                    </p>

                    {catalogProducts.length === 0 ? (
                        <p className="mt-10 text-zinc-500">
                            Todavia no hay productos disponibles.
                        </p>
                    ) : (
                        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {catalogProducts.map((product) => (
                                <Link key={product.id} href={show(product.id)}>
                                    <Card className="h-full transition hover:border-zinc-400">
                                        <CardHeader>
                                            <CardTitle>
                                                {product.name}
                                            </CardTitle>
                                        </CardHeader>
                                    </Card>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
