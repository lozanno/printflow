import { Form, Head, Link } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Pencil, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import ComponentController from '@/actions/App/Http/Controllers/Admin/ComponentController';
import ComponentOptionController from '@/actions/App/Http/Controllers/Admin/ComponentOptionController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { index } from '@/routes/admin/components';
import type { Component } from '@/types';

export default function ComponentsEdit({
    component,
}: {
    component: Component;
}) {
    const [editingOptionId, setEditingOptionId] = useState<number | null>(
        null,
    );

    return (
        <>
            <Head title={`Editar ${component.label}`} />

            <div className="max-w-2xl space-y-8 p-4">
                <Heading
                    title={component.label}
                    description="Edita el componente y administra sus opciones."
                />

                <Form
                    {...ComponentController.update.form(component.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="label">Etiqueta</Label>
                                <Input
                                    id="label"
                                    name="label"
                                    required
                                    defaultValue={component.label}
                                />
                                <InputError message={errors.label} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="code">Codigo interno</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    required
                                    defaultValue={component.code}
                                    className="font-mono"
                                />
                                <InputError message={errors.code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="input_type">
                                    Tipo de campo
                                </Label>
                                <Select
                                    name="input_type"
                                    required
                                    defaultValue={component.input_type}
                                >
                                    <SelectTrigger id="input_type">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="CHOICE">
                                            Opciones (elegir una)
                                        </SelectItem>
                                        <SelectItem value="NUMBER">
                                            Numero
                                        </SelectItem>
                                        <SelectItem value="DIMENSIONS">
                                            Dimensiones (ancho x alto)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.input_type} />
                            </div>

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>

                {component.input_type === 'CHOICE' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Opciones</CardTitle>
                            <CardDescription>
                                Los valores que el cliente podra elegir para
                                este componente.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {component.options &&
                            component.options.length > 0 ? (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Imagen</TableHead>
                                            <TableHead>Etiqueta</TableHead>
                                            <TableHead>Valor</TableHead>
                                            <TableHead className="text-right">
                                                Acciones
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {component.options.map((option, index) =>
                                            editingOptionId === option.id ? (
                                                <TableRow key={option.id}>
                                                    <TableCell colSpan={4}>
                                                        <Form
                                                            {...ComponentOptionController.update.form(
                                                                [
                                                                    component.id,
                                                                    option.id,
                                                                ],
                                                            )}
                                                            onSuccess={() =>
                                                                setEditingOptionId(
                                                                    null,
                                                                )
                                                            }
                                                            className="flex flex-wrap items-end gap-3 py-2"
                                                        >
                                                            {({
                                                                processing,
                                                                errors,
                                                            }) => (
                                                                <>
                                                                    <div className="grid gap-2">
                                                                        <Label
                                                                            htmlFor={`edit_label_${option.id}`}
                                                                        >
                                                                            Etiqueta
                                                                        </Label>
                                                                        <Input
                                                                            id={`edit_label_${option.id}`}
                                                                            name="label"
                                                                            required
                                                                            defaultValue={
                                                                                option.label
                                                                            }
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.label
                                                                            }
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label
                                                                            htmlFor={`edit_value_${option.id}`}
                                                                        >
                                                                            Valor
                                                                        </Label>
                                                                        <Input
                                                                            id={`edit_value_${option.id}`}
                                                                            name="value"
                                                                            required
                                                                            defaultValue={
                                                                                option.value
                                                                            }
                                                                            className="font-mono"
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.value
                                                                            }
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label
                                                                            htmlFor={`edit_image_${option.id}`}
                                                                        >
                                                                            Imagen
                                                                            (opcional)
                                                                        </Label>
                                                                        <Input
                                                                            id={`edit_image_${option.id}`}
                                                                            name="image"
                                                                            type="file"
                                                                            accept="image/jpeg,image/png,image/webp"
                                                                            className="max-w-52"
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.image
                                                                            }
                                                                        />
                                                                    </div>
                                                                    <Button
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        Guardar
                                                                    </Button>
                                                                    <Button
                                                                        type="button"
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        aria-label="Cancelar"
                                                                        onClick={() =>
                                                                            setEditingOptionId(
                                                                                null,
                                                                            )
                                                                        }
                                                                    >
                                                                        <X />
                                                                    </Button>
                                                                </>
                                                            )}
                                                        </Form>
                                                    </TableCell>
                                                </TableRow>
                                            ) : (
                                                <TableRow key={option.id}>
                                                    <TableCell>
                                                        {option.image_url ? (
                                                            <img
                                                                src={
                                                                    option.image_url
                                                                }
                                                                alt=""
                                                                className="h-10 w-10 rounded border object-cover"
                                                            />
                                                        ) : (
                                                            <div className="h-10 w-10 rounded border border-dashed bg-muted" />
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {option.label}
                                                    </TableCell>
                                                    <TableCell className="font-mono text-xs text-muted-foreground">
                                                        {option.value}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <div className="flex justify-end gap-1">
                                                            <Form
                                                                {...ComponentOptionController.move.form(
                                                                    [
                                                                        component.id,
                                                                        option.id,
                                                                    ],
                                                                )}
                                                            >
                                                                {({
                                                                    processing:
                                                                        moving,
                                                                }) => (
                                                                    <>
                                                                        <input
                                                                            type="hidden"
                                                                            name="direction"
                                                                            value="up"
                                                                        />
                                                                        <Button
                                                                            type="submit"
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            disabled={
                                                                                moving ||
                                                                                index ===
                                                                                    0
                                                                            }
                                                                            aria-label={`Subir ${option.label}`}
                                                                        >
                                                                            <ArrowUp />
                                                                        </Button>
                                                                    </>
                                                                )}
                                                            </Form>
                                                            <Form
                                                                {...ComponentOptionController.move.form(
                                                                    [
                                                                        component.id,
                                                                        option.id,
                                                                    ],
                                                                )}
                                                            >
                                                                {({
                                                                    processing:
                                                                        moving,
                                                                }) => (
                                                                    <>
                                                                        <input
                                                                            type="hidden"
                                                                            name="direction"
                                                                            value="down"
                                                                        />
                                                                        <Button
                                                                            type="submit"
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            disabled={
                                                                                moving ||
                                                                                index ===
                                                                                    (component
                                                                                        .options
                                                                                        ?.length ??
                                                                                        0) -
                                                                                        1
                                                                            }
                                                                            aria-label={`Bajar ${option.label}`}
                                                                        >
                                                                            <ArrowDown />
                                                                        </Button>
                                                                    </>
                                                                )}
                                                            </Form>
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="icon"
                                                                aria-label={`Editar ${option.label}`}
                                                                onClick={() =>
                                                                    setEditingOptionId(
                                                                        option.id,
                                                                    )
                                                                }
                                                            >
                                                                <Pencil />
                                                            </Button>
                                                            <Form
                                                                {...ComponentOptionController.destroy.form(
                                                                    [
                                                                        component.id,
                                                                        option.id,
                                                                    ],
                                                                )}
                                                            >
                                                                {({
                                                                    processing:
                                                                        deleting,
                                                                }) => (
                                                                    <Button
                                                                        type="submit"
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        disabled={
                                                                            deleting
                                                                        }
                                                                        aria-label={`Eliminar ${option.label}`}
                                                                    >
                                                                        <Trash2 />
                                                                    </Button>
                                                                )}
                                                            </Form>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ),
                                        )}
                                    </TableBody>
                                </Table>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Todavia no hay opciones.
                                </p>
                            )}

                            <Form
                                {...ComponentOptionController.store.form(
                                    component.id,
                                )}
                                resetOnSuccess
                                className="flex items-end gap-3 border-t pt-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid flex-1 gap-2">
                                            <Label htmlFor="option_label">
                                                Etiqueta
                                            </Label>
                                            <Input
                                                id="option_label"
                                                name="label"
                                                required
                                                placeholder="Laminado brillante"
                                            />
                                            <InputError
                                                message={errors.label}
                                            />
                                        </div>
                                        <div className="grid flex-1 gap-2">
                                            <Label htmlFor="option_value">
                                                Valor
                                            </Label>
                                            <Input
                                                id="option_value"
                                                name="value"
                                                required
                                                placeholder="gloss"
                                                className="font-mono"
                                            />
                                            <InputError
                                                message={errors.value}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="option_image">
                                                Imagen (opcional)
                                            </Label>
                                            <Input
                                                id="option_image"
                                                name="image"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                className="max-w-52"
                                            />
                                            <InputError
                                                message={errors.image}
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
                        </CardContent>
                    </Card>
                )}

                <Button variant="outline" asChild>
                    <Link href={index()}>Volver a componentes</Link>
                </Button>
            </div>
        </>
    );
}

ComponentsEdit.layout = {
    breadcrumbs: [
        { title: 'Componentes', href: index() },
        { title: 'Editar', href: '' },
    ],
};
