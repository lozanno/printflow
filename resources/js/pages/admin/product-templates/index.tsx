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
import { create, destroy, edit, index } from '@/routes/admin/product-templates';
import type { PricingStrategy, ProductTemplate } from '@/types';

const PRICING_STRATEGY_LABELS: Record<PricingStrategy, string> = {
    PER_UNIT_TIERED: 'Por pieza (escalonado)',
    PER_AREA: 'Por area',
    PER_AREA_WITH_SETUP: 'Por area + costo fijo',
};

function handleDelete(productTemplate: ProductTemplate) {
    if (
        !confirm(
            `Eliminar "${productTemplate.name}"? Esta accion no se puede deshacer.`,
        )
    ) {
        return;
    }

    router.delete(destroy(productTemplate.id).url);
}

export default function ProductTemplatesIndex({
    productTemplates,
}: {
    productTemplates: ProductTemplate[];
}) {
    return (
        <>
            <Head title="Plantillas de producto" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Plantillas de producto"
                        description="Que componentes tiene cada tipo de producto y como se calcula su precio."
                    />

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nueva plantilla
                        </Link>
                    </Button>
                </div>

                {productTemplates.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavia no hay plantillas. Crea la primera para empezar
                        a armar tu catalogo.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Codigo</TableHead>
                                <TableHead>Estrategia de precio</TableHead>
                                <TableHead>Componentes</TableHead>
                                <TableHead className="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {productTemplates.map((productTemplate) => (
                                <TableRow key={productTemplate.id}>
                                    <TableCell className="font-medium">
                                        {productTemplate.name}
                                    </TableCell>
                                    <TableCell className="font-mono text-xs text-muted-foreground">
                                        {productTemplate.code}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {
                                                PRICING_STRATEGY_LABELS[
                                                    productTemplate
                                                        .pricing_strategy
                                                ]
                                            }
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {productTemplate.components_count ?? 0}
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
                                                        productTemplate.id,
                                                    )}
                                                >
                                                    Editar
                                                </Link>
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`Eliminar ${productTemplate.name}`}
                                                onClick={() =>
                                                    handleDelete(
                                                        productTemplate,
                                                    )
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

ProductTemplatesIndex.layout = {
    breadcrumbs: [{ title: 'Plantillas de producto', href: index() }],
};
