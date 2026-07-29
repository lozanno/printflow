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
import { create, destroy, edit, index } from '@/routes/admin/components';
import type { Component, InputType } from '@/types';

function handleDelete(component: Component) {
    if (
        !confirm(
            `Eliminar "${component.label}"? Tambien se borran sus opciones. Esta accion no se puede deshacer.`,
        )
    ) {
        return;
    }

    router.delete(destroy(component.id).url);
}

const INPUT_TYPE_LABELS: Record<InputType, string> = {
    CHOICE: 'Opciones',
    NUMBER: 'Numero',
    DIMENSIONS: 'Ancho x alto',
};

export default function ComponentsIndex({
    components,
}: {
    components: Component[];
}) {
    return (
        <>
            <Head title="Componentes" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Componentes"
                        description="Campos reusables de configuracion: cantidad, tamano, acabado..."
                    />

                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nuevo componente
                        </Link>
                    </Button>
                </div>

                {components.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavia no hay componentes. Crea el primero para empezar
                        a armar productos.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Etiqueta</TableHead>
                                <TableHead>Codigo</TableHead>
                                <TableHead>Tipo</TableHead>
                                <TableHead>Opciones</TableHead>
                                <TableHead className="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {components.map((component) => (
                                <TableRow key={component.id}>
                                    <TableCell className="font-medium">
                                        {component.label}
                                    </TableCell>
                                    <TableCell className="font-mono text-xs text-muted-foreground">
                                        {component.code}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {
                                                INPUT_TYPE_LABELS[
                                                    component.input_type
                                                ]
                                            }
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {component.options_count ?? 0}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                asChild
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link href={edit(component.id)}>
                                                    Editar
                                                </Link>
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`Eliminar ${component.label}`}
                                                onClick={() =>
                                                    handleDelete(component)
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

ComponentsIndex.layout = {
    breadcrumbs: [{ title: 'Componentes', href: index() }],
};
