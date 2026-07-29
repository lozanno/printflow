import { Form, Head, Link } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import CatalogProductController from '@/actions/App/Http/Controllers/Admin/CatalogProductController';
import OptionPriceModifierController from '@/actions/App/Http/Controllers/Admin/OptionPriceModifierController';
import PricingTierController from '@/actions/App/Http/Controllers/Admin/PricingTierController';
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
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/admin/catalog-products';
import type { CatalogProduct } from '@/types';

type AvailableOption = {
    id: number;
    label: string;
    value: string;
    component_label: string;
};

const MODIFIER_TYPE_LABELS: Record<string, string> = {
    FIXED_ADD: 'Suma fija',
    PERCENT_MULTIPLY: 'Multiplicador porcentual',
    PER_UNIT_ADD: 'Suma por unidad',
};

export default function CatalogProductsEdit({
    catalogProduct,
    availableOptions,
}: {
    catalogProduct: CatalogProduct;
    availableOptions: AvailableOption[];
}) {
    const strategy = catalogProduct.product_template?.pricing_strategy;
    const tiers = catalogProduct.pricing_profile?.tiers ?? [];
    const modifiers = catalogProduct.pricing_profile?.option_modifiers ?? [];
    const displayName =
        catalogProduct.name_override ?? catalogProduct.product_template?.name;

    return (
        <>
            <Head title={`Editar ${displayName}`} />

            <div className="max-w-2xl space-y-8 p-4">
                <Heading
                    title={displayName ?? 'Producto de catalogo'}
                    description={`Plantilla: ${catalogProduct.product_template?.name}`}
                />

                <Form
                    {...CatalogProductController.update.form(catalogProduct.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name_override">
                                    Nombre para el cliente (opcional)
                                </Label>
                                <Input
                                    id="name_override"
                                    name="name_override"
                                    defaultValue={
                                        catalogProduct.name_override ?? ''
                                    }
                                />
                                <InputError message={errors.name_override} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Descripcion / texto promocional
                                </Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    rows={4}
                                    placeholder="Cuentale al cliente por que comprar contigo: calidad, tiempos de entrega, garantia..."
                                    defaultValue={
                                        catalogProduct.description ?? ''
                                    }
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image">Foto del producto</Label>
                                {catalogProduct.image_url && (
                                    <img
                                        src={catalogProduct.image_url}
                                        alt=""
                                        className="h-32 w-32 rounded-lg border object-cover"
                                    />
                                )}
                                <Input
                                    id="image"
                                    name="image"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                />
                                <InputError message={errors.image} />
                            </div>

                            <div className="flex flex-col gap-2">
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="is_active"
                                        name="is_active"
                                        defaultChecked={
                                            catalogProduct.is_active
                                        }
                                    />
                                    <Label
                                        htmlFor="is_active"
                                        className="font-normal"
                                    >
                                        Activo (visible en el catalogo)
                                    </Label>
                                </div>
                                <InputError message={errors.is_active} />
                            </div>

                            {(strategy === 'PER_AREA' ||
                                strategy === 'PER_AREA_WITH_SETUP') && (
                                <div className="grid gap-2">
                                    <Label htmlFor="rate_per_sqm">
                                        Tarifa por m2
                                    </Label>
                                    <Input
                                        id="rate_per_sqm"
                                        name="rate_per_sqm"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        defaultValue={
                                            catalogProduct.pricing_profile
                                                ?.params?.rate_per_sqm ?? ''
                                        }
                                    />
                                    <InputError message={errors.rate_per_sqm} />
                                </div>
                            )}

                            {strategy === 'PER_AREA_WITH_SETUP' && (
                                <div className="grid gap-2">
                                    <Label htmlFor="setup_fee">
                                        Costo fijo de preparacion
                                    </Label>
                                    <Input
                                        id="setup_fee"
                                        name="setup_fee"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        defaultValue={
                                            catalogProduct.pricing_profile
                                                ?.params?.setup_fee ?? ''
                                        }
                                    />
                                    <InputError message={errors.setup_fee} />
                                </div>
                            )}

                            <Button disabled={processing}>
                                Guardar cambios
                            </Button>
                        </>
                    )}
                </Form>

                {strategy === 'PER_UNIT_TIERED' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Rangos de precio</CardTitle>
                            <CardDescription>
                                El precio por pieza segun la cantidad pedida.
                                Los rangos no deberian dejar huecos ni
                                traslaparse.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {tiers.length > 0 ? (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Desde</TableHead>
                                            <TableHead>Hasta</TableHead>
                                            <TableHead>
                                                Precio por pieza
                                            </TableHead>
                                            <TableHead className="text-right">
                                                Acciones
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {tiers.map((tier) => (
                                            <TableRow key={tier.id}>
                                                <TableCell>
                                                    {tier.min_quantity}
                                                </TableCell>
                                                <TableCell>
                                                    {tier.max_quantity ??
                                                        'Sin limite'}
                                                </TableCell>
                                                <TableCell>
                                                    {tier.unit_price}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Form
                                                        {...PricingTierController.destroy.form(
                                                            [
                                                                catalogProduct.id,
                                                                tier.id,
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
                                                                aria-label="Eliminar rango"
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
                                    Todavia no hay rangos de precio. Sin al
                                    menos uno, este producto no se puede
                                    cotizar.
                                </p>
                            )}

                            <Form
                                {...PricingTierController.store.form(
                                    catalogProduct.id,
                                )}
                                resetOnSuccess
                                className="flex items-end gap-3 border-t pt-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="min_quantity">
                                                Desde
                                            </Label>
                                            <Input
                                                id="min_quantity"
                                                name="min_quantity"
                                                type="number"
                                                min="0"
                                                required
                                                className="w-24"
                                            />
                                            <InputError
                                                message={errors.min_quantity}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="max_quantity">
                                                Hasta (vacio = sin limite)
                                            </Label>
                                            <Input
                                                id="max_quantity"
                                                name="max_quantity"
                                                type="number"
                                                min="0"
                                                className="w-24"
                                            />
                                            <InputError
                                                message={errors.max_quantity}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="unit_price">
                                                Precio por pieza
                                            </Label>
                                            <Input
                                                id="unit_price"
                                                name="unit_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                className="w-28"
                                            />
                                            <InputError
                                                message={errors.unit_price}
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

                {(modifiers.length > 0 || availableOptions.length > 0) && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Modificadores por opcion</CardTitle>
                            <CardDescription>
                                Cuanto cambia el precio cuando el cliente elige
                                cada opcion (ej. laminado brillante, entrega
                                urgente).
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {modifiers.length > 0 ? (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Opcion</TableHead>
                                            <TableHead>Tipo</TableHead>
                                            <TableHead>Valor</TableHead>
                                            <TableHead className="text-right">
                                                Acciones
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {modifiers.map((modifier) => (
                                            <TableRow key={modifier.id}>
                                                <TableCell>
                                                    {
                                                        modifier
                                                            .component_option
                                                            ?.component?.label
                                                    }
                                                    :{' '}
                                                    {
                                                        modifier
                                                            .component_option
                                                            ?.label
                                                    }
                                                </TableCell>
                                                <TableCell>
                                                    {
                                                        MODIFIER_TYPE_LABELS[
                                                            modifier
                                                                .modifier_type
                                                        ]
                                                    }
                                                </TableCell>
                                                <TableCell>
                                                    {modifier.value}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Form
                                                        {...OptionPriceModifierController.destroy.form(
                                                            [
                                                                catalogProduct.id,
                                                                modifier.id,
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
                                                                aria-label="Eliminar modificador"
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
                                    Todavia no hay modificadores.
                                </p>
                            )}

                            {availableOptions.length > 0 && (
                                <Form
                                    {...OptionPriceModifierController.store.form(
                                        catalogProduct.id,
                                    )}
                                    resetOnSuccess
                                    className="flex items-end gap-3 border-t pt-6"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid flex-1 gap-2">
                                                <Label htmlFor="component_option_id">
                                                    Opcion
                                                </Label>
                                                <Select
                                                    name="component_option_id"
                                                    required
                                                >
                                                    <SelectTrigger id="component_option_id">
                                                        <SelectValue placeholder="Selecciona una opcion" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {availableOptions.map(
                                                            (option) => (
                                                                <SelectItem
                                                                    key={
                                                                        option.id
                                                                    }
                                                                    value={option.id.toString()}
                                                                >
                                                                    {
                                                                        option.component_label
                                                                    }
                                                                    :{' '}
                                                                    {
                                                                        option.label
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.component_option_id
                                                    }
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="modifier_type">
                                                    Tipo
                                                </Label>
                                                <Select
                                                    name="modifier_type"
                                                    required
                                                >
                                                    <SelectTrigger id="modifier_type">
                                                        <SelectValue placeholder="Tipo" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="FIXED_ADD">
                                                            Suma fija
                                                        </SelectItem>
                                                        <SelectItem value="PERCENT_MULTIPLY">
                                                            Multiplicador %
                                                        </SelectItem>
                                                        <SelectItem value="PER_UNIT_ADD">
                                                            Suma por unidad
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.modifier_type
                                                    }
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="value">
                                                    Valor
                                                </Label>
                                                <Input
                                                    id="value"
                                                    name="value"
                                                    type="number"
                                                    step="0.0001"
                                                    required
                                                    className="w-28"
                                                />
                                                <InputError
                                                    message={errors.value}
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
                            )}
                        </CardContent>
                    </Card>
                )}

                <Button variant="outline" asChild>
                    <Link href={index()}>Volver al catalogo</Link>
                </Button>
            </div>
        </>
    );
}

CatalogProductsEdit.layout = {
    breadcrumbs: [
        { title: 'Catalogo', href: index() },
        { title: 'Editar', href: '' },
    ],
};
