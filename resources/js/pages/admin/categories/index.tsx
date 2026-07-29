import { Head, Link, router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, destroy, edit, index } from '@/routes/admin/categories';
import type { Category } from '@/types';

function handleDelete(category: Category) {
    if (
        !confirm(
            `Eliminar "${category.name}"? Esta accion no se puede deshacer.`,
        )
    ) {
        return;
    }

    router.delete(destroy(category.id).url);
}

export default function CategoriesIndex({
    categories,
}: {
    categories: Category[];
}) {
    return (
        <>
            <Head title="Categorias" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Categorias"
                        description="Agrupa productos del catalogo para mostrarlos por seccion en el sitio."
                    />

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nueva categoria
                        </Link>
                    </Button>
                </div>

                {categories.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavia no hay categorias. Crea la primera para
                        empezar a organizar el catalogo.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>URL</TableHead>
                                <TableHead>Productos</TableHead>
                                <TableHead className="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {categories.map((category) => (
                                <TableRow key={category.id}>
                                    <TableCell className="font-medium">
                                        {category.name}
                                    </TableCell>
                                    <TableCell className="font-mono text-xs text-muted-foreground">
                                        /{category.slug}
                                    </TableCell>
                                    <TableCell>
                                        {category.catalog_products_count ?? 0}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link href={edit(category.id)}>
                                                    Editar
                                                </Link>
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`Eliminar ${category.name}`}
                                                onClick={() =>
                                                    handleDelete(category)
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

CategoriesIndex.layout = {
    breadcrumbs: [{ title: 'Categorias', href: index() }],
};
