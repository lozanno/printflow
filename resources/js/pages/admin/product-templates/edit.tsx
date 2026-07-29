import { Form, Head, Link } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import ProductTemplateComponentController from '@/actions/App/Http/Controllers/Admin/ProductTemplateComponentController';
import ProductTemplateController from '@/actions/App/Http/Controllers/Admin/ProductTemplateController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/admin/product-templates';
import type { Component, ProductTemplate } from '@/types';

export default function ProductTemplatesEdit({
    productTemplate,
    availableComponents,
}: {
    productTemplate: ProductTemplate;
    availableComponents: Component[];
}) {
    const components = productTemplate.components ?? [];

    return (
        <>
            <Head title={`Editar ${productTemplate.name}`} />

            <div className="max-w-2xl space-y-8 p-4">
                <Heading
                    title={productTemplate.name}
                    description="Edita la plantilla y administra sus componentes."
                />

                <Form
                    {...ProductTemplateController.update.form(
                        productTemplate.id,
                    )}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    defaultValue={productTemplate.name}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="code">Codigo interno</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    required
                                    defaultValue={productTemplate.code}
                                    className="font-mono"
                                />
                                <InputError message={errors.code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="pricing_strategy">
                                    Estrategia de precio
                                </Label>
                                <Select
                                    name="pricing_strategy"
                                    required
                                    defaultValue={
                                        productTemplate.pricing_strategy
                                    }
                                >
                                    <SelectTrigger id="pricing_strategy">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="PER_UNIT_TIERED">
                                            Por pieza (escalonado por cantidad)
                                        </SelectItem>
                                        <SelectItem value="PER_AREA">
                                            Por area
                                        </SelectItem>
                                        <SelectItem value="PER_AREA_WITH_SETUP">
                                            Por area + costo fijo
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.pricing_strategy} />
                            </div>

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>

                <Card>
                    <CardHeader>
                        <CardTitle>Componentes</CardTitle>
                        <CardDescription>
                            Los campos de configuracion que tendra este
                            producto, en el orden en que se agregan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {components.length > 0 ? (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Etiqueta</TableHead>
                                        <TableHead>Codigo</TableHead>
                                        <TableHead>Obligatorio</TableHead>
                                        <TableHead className="text-right">
                                            Acciones
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {components.map((component) => (
                                        <TableRow key={component.id}>
                                            <TableCell>
                                                {component.label}
                                            </TableCell>
                                            <TableCell className="font-mono text-xs text-muted-foreground">
                                                {component.code}
                                            </TableCell>
                                            <TableCell>
                                                {component.pivot.is_required ? (
                                                    <Badge variant="secondary">
                                                        Si
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline">
                                                        No
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Form
                                                    {...ProductTemplateComponentController.destroy.form(
                                                        [
                                                            productTemplate.id,
                                                            component.id,
                                                        ],
                                                    )}
                                                >
                                                    {({
                                                        processing: removing,
                                                    }) => (
                                                        <Button
                                                            type="submit"
                                                            variant="ghost"
                                                            size="icon"
                                                            disabled={removing}
                                                            aria-label={`Quitar ${component.label}`}
                                                        >
                                                            <Trash2 />
                                                        </Button>
                                                    )}
                                                </Form>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Todavia no hay componentes en esta plantilla.
                            </p>
                        )}

                        {availableComponents.length > 0 ? (
                            <Form
                                {...ProductTemplateComponentController.store.form(
                                    productTemplate.id,
                                )}
                                resetOnSuccess
                                className="flex items-end gap-3 border-t pt-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid flex-1 gap-2">
                                            <Label htmlFor="component_id">
                                                Componente
                                            </Label>
                                            <Select
                                                name="component_id"
                                                required
                                            >
                                                <SelectTrigger id="component_id">
                                                    <SelectValue placeholder="Selecciona un componente" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {availableComponents.map(
                                                        (component) => (
                                                            <SelectItem
                                                                key={
                                                                    component.id
                                                                }
                                                                value={component.id.toString()}
                                                            >
                                                                {
                                                                    component.label
                                                                }
                                                            </SelectItem>
                                                        ),
                                                    )}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.component_id}
                                            />
                                        </div>

                                        <div className="flex flex-col gap-2 pb-2">
                                            <div className="flex items-center gap-2">
                                                <Checkbox
                                                    id="is_required"
                                                    name="is_required"
                                                    defaultChecked
                                                />
                                                <Label
                                                    htmlFor="is_required"
                                                    className="font-normal"
                                                >
                                                    Obligatorio
                                                </Label>
                                            </div>
                                            <InputError
                                                message={errors.is_required}
                                            />
                                        </div>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Agregar
                                        </Button>
                                    </>
                                )}
                            </Form>
                        ) : (
                            <p className="border-t pt-6 text-sm text-muted-foreground">
                                Ya agregaste todos los componentes disponibles.
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Button variant="outline" asChild>
                    <Link href={index()}>Volver a plantillas</Link>
                </Button>
            </div>
        </>
    );
}

ProductTemplatesEdit.layout = {
    breadcrumbs: [
        { title: 'Plantillas de producto', href: index() },
        { title: 'Editar', href: '' },
    ],
};
