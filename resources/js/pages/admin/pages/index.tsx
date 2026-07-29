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
import { create, destroy, edit, index } from '@/routes/admin/pages';
import type { Page } from '@/types';

function handleDelete(page: Page) {
    if (
        !confirm(
            `Eliminar "${page.title}"? Esta accion no se puede deshacer.`,
        )
    ) {
        return;
    }

    router.delete(destroy(page.id).url);
}

export default function PagesIndex({ pages }: { pages: Page[] }) {
    return (
        <>
            <Head title="Paginas" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Paginas"
                        description="Contenido estatico como Quienes somos, Terminos y condiciones, etc."
                    />

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nueva pagina
                        </Link>
                    </Button>
                </div>

                {pages.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavia no hay paginas. Crea la primera para empezar.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Titulo</TableHead>
                                <TableHead>URL</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pages.map((page) => (
                                <TableRow key={page.id}>
                                    <TableCell className="font-medium">
                                        {page.title}
                                    </TableCell>
                                    <TableCell className="font-mono text-xs text-muted-foreground">
                                        /{page.slug}
                                    </TableCell>
                                    <TableCell>
                                        {page.is_published ? (
                                            <Badge variant="secondary">
                                                Publicada
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline">
                                                Borrador
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
                                                <Link href={edit(page.id)}>
                                                    Editar
                                                </Link>
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`Eliminar ${page.title}`}
                                                onClick={() =>
                                                    handleDelete(page)
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

PagesIndex.layout = {
    breadcrumbs: [{ title: 'Paginas', href: index() }],
};
