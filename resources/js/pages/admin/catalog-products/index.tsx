import { Head, Link, router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, destroy, edit, index } from '@/routes/admin/catalog-products';
import type { CatalogProduct } from '@/types';

function isPriced(catalogProduct: CatalogProduct): boolean {
    const profile = catalogProduct.pricing_profile;

    if (!profile) {
        return false;
    }

    if (
        catalogProduct.product_template?.pricing_strategy === 'PER_UNIT_TIERED'
    ) {
        return (profile.tiers?.length ?? 0) > 0;
    }

    return profile.params !== null;
}

function handleDelete(catalogProduct: CatalogProduct) {
    const name =
        catalogProduct.name_override ??
        catalogProduct.product_template?.name ??
        'este producto';

    if (!confirm(`Eliminar "${name}"? Esta accion no se puede deshacer.`)) {
        return;
    }

    router.delete(destroy(catalogProduct.id).url);
}

export default function CatalogProductsIndex({
    catalogProducts,
}: {
    catalogProducts: CatalogProduct[];
}) {
    return (
        <>
            <Head title="Catalogo" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Catalogo"
                        description="Los productos que tu tienda vende, con su precio."
                    />

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nuevo producto
                        </Link>
                    </Button>
                </div>

                {catalogProducts.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavia no hay productos en el catalogo. Crea el primero
                        a partir de una plantilla.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Plantilla</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead>Precio</TableHead>
                                <TableHead className="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {catalogProducts.map((catalogProduct) => (
                                <TableRow key={catalogProduct.id}>
                                    <TableCell className="font-medium">
                                        {catalogProduct.name_override ??
                                            catalogProduct.product_template
                                                ?.name}
                                    </TableCell>
                                    <TableCell className="text-xs text-muted-foreground">
                                        {catalogProduct.product_template?.name}
                                    </TableCell>
                                    <TableCell>
                                        {catalogProduct.is_active ? (
                                            <Badge variant="secondary">
                                                Activo
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline">
                                                Inactivo
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {isPriced(catalogProduct) ? (
                                            <Badge variant="secondary">
                                                Configurado
                                            </Badge>
                                        ) : (
                                            <Badge variant="destructive">
                                                Falta precio
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link
                                                    href={edit(
                                                        catalogProduct.id,
                                                    )}
                                                >
                                                    Editar
                                                </Link>
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label="Eliminar producto"
                                                onClick={() =>
                                                    handleDelete(catalogProduct)
                                                }
                                            >
                                                <Trash2 />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                )}
            </div>
        </>
    );
}

CatalogProductsIndex.layout = {
    breadcrumbs: [{ title: 'Catalogo', href: index() }],
};
