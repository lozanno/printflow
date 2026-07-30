import { Form, Head, Link } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUp,
    Pencil,
    Star,
    Trash2,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import CatalogProductController from '@/actions/App/Http/Controllers/Admin/CatalogProductController';
import CatalogProductFaqController from '@/actions/App/Http/Controllers/Admin/CatalogProductFaqController';
import CatalogProductReviewController from '@/actions/App/Http/Controllers/Admin/CatalogProductReviewController';
import OptionPriceModifierController from '@/actions/App/Http/Controllers/Admin/OptionPriceModifierController';
import PricingTierController from '@/actions/App/Http/Controllers/Admin/PricingTierController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { RichTextEditor } from '@/components/rich-text-editor';
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
import { cn } from '@/lib/utils';
import { index } from '@/routes/admin/catalog-products';
import type { CatalogProduct, Category } from '@/types';

type OptionModifier = {
    id: number;
    modifier_type: string;
    value: string;
};

type NavSection = {
    id: string;
    label: string;
};

// Highlights whichever section is currently scrolled into view in the
// right-hand submenu, so it reads as a table of contents rather than a
// static list of anchors.
function useActiveSection(sections: NavSection[]): string {
    const [activeId, setActiveId] = useState(sections[0]?.id ?? '');
    const sectionIds = sections.map((section) => section.id).join(',');

    useEffect(() => {
        const ids = sectionIds.split(',').filter(Boolean);
        const elements = ids
            .map((id) => document.getElementById(id))
            .filter((element): element is HTMLElement => element !== null);

        if (elements.length === 0) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort(
                        (a, b) =>
                            a.boundingClientRect.top -
                            b.boundingClientRect.top,
                    );

                if (visible.length > 0) {
                    setActiveId(visible[0].target.id);
                }
            },
            { rootMargin: '-96px 0px -70% 0px' },
        );

        elements.forEach((element) => observer.observe(element));

        return () => observer.disconnect();
    }, [sectionIds]);

    return activeId;
}

function SectionNav({ sections }: { sections: NavSection[] }) {
    const activeId = useActiveSection(sections);

    return (
        <div className="hidden shrink-0 lg:block lg:w-48">
            <nav className="sticky top-4">
                <ul className="space-y-1 border-l">
                    {sections.map((section) => (
                        <li key={section.id}>
                            <a
                                href={`#${section.id}`}
                                className={cn(
                                    '-ml-px block border-l-2 px-3 py-1.5 text-sm transition-colors',
                                    activeId === section.id
                                        ? 'border-foreground font-medium text-foreground'
                                        : 'border-transparent text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {section.label}
                            </a>
                        </li>
                    ))}
                </ul>
            </nav>
        </div>
    );
}

function RatingSelect({
    id,
    name,
    defaultValue,
}: {
    id: string;
    name: string;
    defaultValue?: number;
}) {
    return (
        <Select name={name} defaultValue={String(defaultValue ?? 5)}>
            <SelectTrigger id={id} className="w-28">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {[5, 4, 3, 2, 1].map((value) => (
                    <SelectItem key={value} value={String(value)}>
                        {value} {value === 1 ? 'estrella' : 'estrellas'}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

function RatingStars({ rating }: { rating: number }) {
    return (
        <div className="flex gap-0.5" aria-label={`${rating} de 5 estrellas`}>
            {[1, 2, 3, 4, 5].map((value) => (
                <Star
                    key={value}
                    className={cn(
                        'size-4',
                        value <= rating
                            ? 'fill-amber-400 text-amber-400'
                            : 'text-muted-foreground',
                    )}
                />
            ))}
        </div>
    );
}

export default function CatalogProductsEdit({
    catalogProduct,
    optionModifiersByOptionId,
    availableCategories,
}: {
    catalogProduct: CatalogProduct;
    optionModifiersByOptionId: Record<number, OptionModifier>;
    availableCategories: Category[];
}) {
    const [editingTierId, setEditingTierId] = useState<number | null>(null);
    const [editingFaqId, setEditingFaqId] = useState<number | null>(null);
    const [editingReviewId, setEditingReviewId] = useState<number | null>(
        null,
    );
    const strategy = catalogProduct.product_template?.pricing_strategy;
    const tiers = catalogProduct.pricing_profile?.tiers ?? [];
    const faqs = catalogProduct.faqs ?? [];
    const reviews = catalogProduct.reviews ?? [];
    const priceableComponents = (
        catalogProduct.product_template?.components ?? []
    ).filter(
        (component) =>
            component.input_type === 'CHOICE' &&
            (component.options?.length ?? 0) > 0,
    );
    const displayName =
        catalogProduct.name_override ?? catalogProduct.product_template?.name;

    const sections: NavSection[] = [
        { id: 'general', label: 'General' },
        ...(strategy === 'PER_UNIT_TIERED'
            ? [{ id: 'precios-tiers', label: 'Rangos de precio' }]
            : []),
        ...(priceableComponents.length > 0
            ? [{ id: 'precios-opciones', label: 'Precios por opcion' }]
            : []),
        { id: 'info-libre', label: 'Informacion libre' },
        { id: 'faqs', label: 'Preguntas frecuentes' },
        { id: 'reviews', label: 'Reseñas' },
    ];

    return (
        <>
            <Head title={`Editar ${displayName}`} />

            <div className="max-w-5xl p-4">
                <Heading
                    title={displayName ?? 'Producto de catalogo'}
                    description={`Plantilla: ${catalogProduct.product_template?.name}`}
                />

                <div className="mt-8 flex gap-10">
                    <div className="min-w-0 flex-1 space-y-8">
                        <section id="general" className="scroll-mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>General</CardTitle>
                                    <CardDescription>
                                        Nombre, precio base, imagen y
                                        categorias.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Form
                                        {...CatalogProductController.update.form(
                                            catalogProduct.id,
                                        )}
                                        className="space-y-6"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="name_override">
                                                        Nombre para el cliente
                                                        (opcional)
                                                    </Label>
                                                    <Input
                                                        id="name_override"
                                                        name="name_override"
                                                        defaultValue={
                                                            catalogProduct.name_override ??
                                                            ''
                                                        }
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.name_override
                                                        }
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label htmlFor="slug">
                                                        URL amigable
                                                    </Label>
                                                    <Input
                                                        id="slug"
                                                        name="slug"
                                                        required
                                                        defaultValue={
                                                            catalogProduct.slug ??
                                                            ''
                                                        }
                                                        className="font-mono"
                                                    />
                                                    <InputError
                                                        message={errors.slug}
                                                    />
                                                    <p className="text-xs text-muted-foreground">
                                                        Como se vera en la
                                                        direccion: localhost/
                                                        {catalogProduct.slug ||
                                                            '...'}
                                                    </p>
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label htmlFor="description">
                                                        Descripcion / texto
                                                        promocional
                                                    </Label>
                                                    <Textarea
                                                        id="description"
                                                        name="description"
                                                        rows={4}
                                                        placeholder="Cuentale al cliente por que comprar contigo: calidad, tiempos de entrega, garantia..."
                                                        defaultValue={
                                                            catalogProduct.description ??
                                                            ''
                                                        }
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.description
                                                        }
                                                    />
                                                </div>

                                                {availableCategories.length >
                                                    0 && (
                                                    <div className="grid gap-2">
                                                        <Label>
                                                            Categorias
                                                        </Label>
                                                        <div className="flex flex-col gap-2">
                                                            {availableCategories.map(
                                                                (
                                                                    category,
                                                                ) => (
                                                                    <div
                                                                        key={
                                                                            category.id
                                                                        }
                                                                        className="flex items-center gap-2"
                                                                    >
                                                                        <Checkbox
                                                                            id={`category_${category.id}`}
                                                                            name="category_ids[]"
                                                                            value={
                                                                                category.id
                                                                            }
                                                                            defaultChecked={catalogProduct.categories?.some(
                                                                                (
                                                                                    c,
                                                                                ) =>
                                                                                    c.id ===
                                                                                    category.id,
                                                                            )}
                                                                        />
                                                                        <Label
                                                                            htmlFor={`category_${category.id}`}
                                                                            className="font-normal"
                                                                        >
                                                                            {
                                                                                category.name
                                                                            }
                                                                        </Label>
                                                                    </div>
                                                                ),
                                                            )}
                                                        </div>
                                                        <InputError
                                                            message={
                                                                errors.category_ids
                                                            }
                                                        />
                                                    </div>
                                                )}

                                                <div className="flex flex-col gap-2">
                                                    <div className="flex items-center gap-2">
                                                        <Checkbox
                                                            id="is_featured"
                                                            name="is_featured"
                                                            defaultChecked={
                                                                catalogProduct.is_featured
                                                            }
                                                        />
                                                        <Label
                                                            htmlFor="is_featured"
                                                            className="font-normal"
                                                        >
                                                            Destacado (aparece
                                                            en el carrusel de
                                                            otros productos)
                                                        </Label>
                                                    </div>
                                                    <InputError
                                                        message={
                                                            errors.is_featured
                                                        }
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label htmlFor="image">
                                                        Foto del producto
                                                    </Label>
                                                    {catalogProduct.image_url && (
                                                        <img
                                                            src={
                                                                catalogProduct.image_url
                                                            }
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
                                                    <InputError
                                                        message={errors.image}
                                                    />
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
                                                            Activo (visible en
                                                            el catalogo)
                                                        </Label>
                                                    </div>
                                                    <InputError
                                                        message={
                                                            errors.is_active
                                                        }
                                                    />
                                                </div>

                                                {(strategy === 'PER_AREA' ||
                                                    strategy ===
                                                        'PER_AREA_WITH_SETUP') && (
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
                                                                catalogProduct
                                                                    .pricing_profile
                                                                    ?.params
                                                                    ?.rate_per_sqm ??
                                                                ''
                                                            }
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.rate_per_sqm
                                                            }
                                                        />
                                                    </div>
                                                )}

                                                {strategy ===
                                                    'PER_AREA_WITH_SETUP' && (
                                                    <div className="grid gap-2">
                                                        <Label htmlFor="setup_fee">
                                                            Costo fijo de
                                                            preparacion
                                                        </Label>
                                                        <Input
                                                            id="setup_fee"
                                                            name="setup_fee"
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            defaultValue={
                                                                catalogProduct
                                                                    .pricing_profile
                                                                    ?.params
                                                                    ?.setup_fee ??
                                                                ''
                                                            }
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.setup_fee
                                                            }
                                                        />
                                                    </div>
                                                )}

                                                <Button
                                                    disabled={processing}
                                                >
                                                    Guardar cambios
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        </section>

                        {strategy === 'PER_UNIT_TIERED' && (
                            <section
                                id="precios-tiers"
                                className="scroll-mt-4"
                            >
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
                                            Rangos de precio
                                        </CardTitle>
                                        <CardDescription>
                                            El precio por pieza segun la
                                            cantidad pedida. Los rangos no
                                            deberian dejar huecos ni
                                            traslaparse.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-6">
                                        {tiers.length > 0 ? (
                                            <Table>
                                                <TableHeader>
                                                    <TableRow>
                                                        <TableHead>
                                                            Desde
                                                        </TableHead>
                                                        <TableHead>
                                                            Hasta
                                                        </TableHead>
                                                        <TableHead>
                                                            Precio por pieza
                                                        </TableHead>
                                                        <TableHead>
                                                            Ajuste %
                                                        </TableHead>
                                                        <TableHead className="text-right">
                                                            Acciones
                                                        </TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {tiers.map((tier) =>
                                                        editingTierId ===
                                                        tier.id ? (
                                                            <TableRow
                                                                key={tier.id}
                                                            >
                                                                <TableCell
                                                                    colSpan={
                                                                        5
                                                                    }
                                                                >
                                                                    <Form
                                                                        {...PricingTierController.update.form(
                                                                            [
                                                                                catalogProduct.id,
                                                                                tier.id,
                                                                            ],
                                                                        )}
                                                                        onSuccess={() =>
                                                                            setEditingTierId(
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
                                                                                        htmlFor={`edit_min_quantity_${tier.id}`}
                                                                                    >
                                                                                        Desde
                                                                                    </Label>
                                                                                    <Input
                                                                                        id={`edit_min_quantity_${tier.id}`}
                                                                                        name="min_quantity"
                                                                                        type="number"
                                                                                        min="0"
                                                                                        required
                                                                                        defaultValue={
                                                                                            tier.min_quantity
                                                                                        }
                                                                                        className="w-24"
                                                                                    />
                                                                                    <InputError
                                                                                        message={
                                                                                            errors.min_quantity
                                                                                        }
                                                                                    />
                                                                                </div>
                                                                                <div className="grid gap-2">
                                                                                    <Label
                                                                                        htmlFor={`edit_max_quantity_${tier.id}`}
                                                                                    >
                                                                                        Hasta
                                                                                    </Label>
                                                                                    <Input
                                                                                        id={`edit_max_quantity_${tier.id}`}
                                                                                        name="max_quantity"
                                                                                        type="number"
                                                                                        min="0"
                                                                                        defaultValue={
                                                                                            tier.max_quantity ??
                                                                                            ''
                                                                                        }
                                                                                        className="w-24"
                                                                                    />
                                                                                    <InputError
                                                                                        message={
                                                                                            errors.max_quantity
                                                                                        }
                                                                                    />
                                                                                </div>
                                                                                <div className="grid gap-2">
                                                                                    <Label
                                                                                        htmlFor={`edit_unit_price_${tier.id}`}
                                                                                    >
                                                                                        Precio
                                                                                        por
                                                                                        pieza
                                                                                    </Label>
                                                                                    <Input
                                                                                        id={`edit_unit_price_${tier.id}`}
                                                                                        name="unit_price"
                                                                                        type="number"
                                                                                        step="0.01"
                                                                                        min="0"
                                                                                        required
                                                                                        defaultValue={
                                                                                            tier.unit_price
                                                                                        }
                                                                                        className="w-28"
                                                                                    />
                                                                                    <InputError
                                                                                        message={
                                                                                            errors.unit_price
                                                                                        }
                                                                                    />
                                                                                </div>
                                                                                <div className="grid gap-2">
                                                                                    <Label
                                                                                        htmlFor={`edit_adjustment_percent_${tier.id}`}
                                                                                    >
                                                                                        Ajuste
                                                                                        %
                                                                                    </Label>
                                                                                    <Input
                                                                                        id={`edit_adjustment_percent_${tier.id}`}
                                                                                        name="adjustment_percent"
                                                                                        type="number"
                                                                                        step="0.001"
                                                                                        defaultValue={
                                                                                            tier.adjustment_percent ??
                                                                                            ''
                                                                                        }
                                                                                        className="w-24"
                                                                                    />
                                                                                    <InputError
                                                                                        message={
                                                                                            errors.adjustment_percent
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
                                                                                        setEditingTierId(
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
                                                            <TableRow
                                                                key={tier.id}
                                                            >
                                                                <TableCell>
                                                                    {
                                                                        tier.min_quantity
                                                                    }
                                                                </TableCell>
                                                                <TableCell>
                                                                    {tier.max_quantity ??
                                                                        'Sin limite'}
                                                                </TableCell>
                                                                <TableCell>
                                                                    {
                                                                        tier.unit_price
                                                                    }
                                                                </TableCell>
                                                                <TableCell>
                                                                    {tier.adjustment_percent ??
                                                                        '—'}
                                                                </TableCell>
                                                                <TableCell className="text-right">
                                                                    <div className="flex justify-end gap-1">
                                                                        <Button
                                                                            type="button"
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            aria-label="Editar rango"
                                                                            onClick={() =>
                                                                                setEditingTierId(
                                                                                    tier.id,
                                                                                )
                                                                            }
                                                                        >
                                                                            <Pencil />
                                                                        </Button>
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
                                                                    </div>
                                                                </TableCell>
                                                            </TableRow>
                                                        ),
                                                    )}
                                                </TableBody>
                                            </Table>
                                        ) : (
                                            <p className="text-sm text-muted-foreground">
                                                Todavia no hay rangos de
                                                precio. Sin al menos uno,
                                                este producto no se puede
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
                                                            message={
                                                                errors.min_quantity
                                                            }
                                                        />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label htmlFor="max_quantity">
                                                            Hasta (vacio = sin
                                                            limite)
                                                        </Label>
                                                        <Input
                                                            id="max_quantity"
                                                            name="max_quantity"
                                                            type="number"
                                                            min="0"
                                                            className="w-24"
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.max_quantity
                                                            }
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
                                                            message={
                                                                errors.unit_price
                                                            }
                                                        />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label htmlFor="adjustment_percent">
                                                            Ajuste % (opcional)
                                                        </Label>
                                                        <Input
                                                            id="adjustment_percent"
                                                            name="adjustment_percent"
                                                            type="number"
                                                            step="0.001"
                                                            className="w-24"
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.adjustment_percent
                                                            }
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
                            </section>
                        )}

                        {priceableComponents.length > 0 && (
                            <section
                                id="precios-opciones"
                                className="scroll-mt-4"
                            >
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
                                            Precios por opcion
                                        </CardTitle>
                                        <CardDescription>
                                            Cuanto cambia el precio cuando el
                                            cliente elige cada opcion. Dejalo
                                            vacio si la opcion no cambia el
                                            precio.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-8">
                                        {priceableComponents.map(
                                            (component) => (
                                                <div
                                                    key={component.id}
                                                    className="space-y-3"
                                                >
                                                    <h3 className="text-sm font-semibold text-foreground">
                                                        {component.label}
                                                    </h3>
                                                    <div className="space-y-4">
                                                        {(
                                                            component.options ??
                                                            []
                                                        ).map((option) => {
                                                            const existing =
                                                                optionModifiersByOptionId[
                                                                    option.id
                                                                ];

                                                            return (
                                                                <div
                                                                    key={
                                                                        option.id
                                                                    }
                                                                    className="flex flex-wrap items-end gap-3 border-t pt-4 first:border-t-0 first:pt-0"
                                                                >
                                                                    <Form
                                                                        {...(existing
                                                                            ? OptionPriceModifierController.update.form(
                                                                                  [
                                                                                      catalogProduct.id,
                                                                                      existing.id,
                                                                                  ],
                                                                              )
                                                                            : OptionPriceModifierController.store.form(
                                                                                  catalogProduct.id,
                                                                              ))}
                                                                        className="flex flex-1 flex-wrap items-end gap-3"
                                                                    >
                                                                        {({
                                                                            processing,
                                                                            errors,
                                                                        }) => (
                                                                            <>
                                                                                {!existing && (
                                                                                    <input
                                                                                        type="hidden"
                                                                                        name="component_option_id"
                                                                                        value={
                                                                                            option.id
                                                                                        }
                                                                                    />
                                                                                )}

                                                                                <div className="min-w-32 flex-1 pb-2 text-sm font-medium">
                                                                                    {
                                                                                        option.label
                                                                                    }
                                                                                </div>

                                                                                <div className="grid gap-2">
                                                                                    <Label
                                                                                        htmlFor={`modifier_type_${option.id}`}
                                                                                    >
                                                                                        Tipo
                                                                                    </Label>
                                                                                    <Select
                                                                                        name="modifier_type"
                                                                                        defaultValue={
                                                                                            existing?.modifier_type ??
                                                                                            'FIXED_ADD'
                                                                                        }
                                                                                    >
                                                                                        <SelectTrigger
                                                                                            id={`modifier_type_${option.id}`}
                                                                                            className="w-44"
                                                                                        >
                                                                                            <SelectValue />
                                                                                        </SelectTrigger>
                                                                                        <SelectContent>
                                                                                            <SelectItem value="FIXED_ADD">
                                                                                                Suma
                                                                                                fija
                                                                                            </SelectItem>
                                                                                            <SelectItem value="PERCENT_MULTIPLY">
                                                                                                Multiplicador
                                                                                                %
                                                                                            </SelectItem>
                                                                                            <SelectItem value="PER_UNIT_ADD">
                                                                                                Suma
                                                                                                por
                                                                                                unidad
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
                                                                                    <Label
                                                                                        htmlFor={`value_${option.id}`}
                                                                                    >
                                                                                        Valor
                                                                                    </Label>
                                                                                    <Input
                                                                                        id={`value_${option.id}`}
                                                                                        name="value"
                                                                                        type="number"
                                                                                        step="0.0001"
                                                                                        defaultValue={
                                                                                            existing?.value ??
                                                                                            ''
                                                                                        }
                                                                                        className="w-28"
                                                                                    />
                                                                                    <InputError
                                                                                        message={
                                                                                            errors.value
                                                                                        }
                                                                                    />
                                                                                </div>

                                                                                <Button
                                                                                    type="submit"
                                                                                    variant="outline"
                                                                                    disabled={
                                                                                        processing
                                                                                    }
                                                                                >
                                                                                    Guardar
                                                                                </Button>
                                                                            </>
                                                                        )}
                                                                    </Form>

                                                                    {existing && (
                                                                        <Form
                                                                            {...OptionPriceModifierController.destroy.form(
                                                                                [
                                                                                    catalogProduct.id,
                                                                                    existing.id,
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
                                                                                    aria-label={`Quitar precio de ${option.label}`}
                                                                                >
                                                                                    <Trash2 />
                                                                                </Button>
                                                                            )}
                                                                        </Form>
                                                                    )}
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                </div>
                                            ),
                                        )}
                                    </CardContent>
                                </Card>
                            </section>
                        )}

                        <section id="info-libre" className="scroll-mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Informacion libre</CardTitle>
                                    <CardDescription>
                                        Contenido adicional debajo del precio
                                        en la pagina del producto:
                                        especificaciones, cuidados, garantia...
                                        Usa el boton de seccion plegable para
                                        agrupar contenido largo.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Form
                                        {...CatalogProductController.updateDetails.form(
                                            catalogProduct.id,
                                        )}
                                        className="space-y-4"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <RichTextEditor
                                                    name="details_content"
                                                    defaultValue={
                                                        catalogProduct.details_content
                                                    }
                                                />
                                                <InputError
                                                    message={
                                                        errors.details_content
                                                    }
                                                />
                                                <Button disabled={processing}>
                                                    Guardar informacion
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        </section>

                        <section id="faqs" className="scroll-mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        Preguntas frecuentes
                                    </CardTitle>
                                    <CardDescription>
                                        Se muestran siempre desplegadas en la
                                        pagina del producto, sin necesidad de
                                        hacer clic.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    {faqs.length > 0 ? (
                                        <div className="space-y-3">
                                            {faqs.map((faq, faqIndex) =>
                                                editingFaqId === faq.id ? (
                                                    <Form
                                                        key={faq.id}
                                                        {...CatalogProductFaqController.update.form(
                                                            [
                                                                catalogProduct.id,
                                                                faq.id,
                                                            ],
                                                        )}
                                                        onSuccess={() =>
                                                            setEditingFaqId(
                                                                null,
                                                            )
                                                        }
                                                        className="space-y-3 rounded-lg border p-4"
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <div className="grid gap-2">
                                                                    <Label
                                                                        htmlFor={`edit_question_${faq.id}`}
                                                                    >
                                                                        Pregunta
                                                                    </Label>
                                                                    <Input
                                                                        id={`edit_question_${faq.id}`}
                                                                        name="question"
                                                                        required
                                                                        defaultValue={
                                                                            faq.question
                                                                        }
                                                                    />
                                                                    <InputError
                                                                        message={
                                                                            errors.question
                                                                        }
                                                                    />
                                                                </div>
                                                                <div className="grid gap-2">
                                                                    <Label
                                                                        htmlFor={`edit_answer_${faq.id}`}
                                                                    >
                                                                        Respuesta
                                                                    </Label>
                                                                    <Textarea
                                                                        id={`edit_answer_${faq.id}`}
                                                                        name="answer"
                                                                        required
                                                                        rows={
                                                                            3
                                                                        }
                                                                        defaultValue={
                                                                            faq.answer
                                                                        }
                                                                    />
                                                                    <InputError
                                                                        message={
                                                                            errors.answer
                                                                        }
                                                                    />
                                                                </div>
                                                                <div className="flex gap-2">
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
                                                                            setEditingFaqId(
                                                                                null,
                                                                            )
                                                                        }
                                                                    >
                                                                        <X />
                                                                    </Button>
                                                                </div>
                                                            </>
                                                        )}
                                                    </Form>
                                                ) : (
                                                    <div
                                                        key={faq.id}
                                                        className="flex items-start justify-between gap-3 rounded-lg border p-4"
                                                    >
                                                        <div className="min-w-0 flex-1">
                                                            <p className="font-medium">
                                                                {faq.question}
                                                            </p>
                                                            <p className="mt-1 text-sm text-muted-foreground">
                                                                {faq.answer}
                                                            </p>
                                                        </div>
                                                        <div className="flex shrink-0 gap-1">
                                                            <Form
                                                                {...CatalogProductFaqController.move.form(
                                                                    [
                                                                        catalogProduct.id,
                                                                        faq.id,
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
                                                                                faqIndex ===
                                                                                    0
                                                                            }
                                                                            aria-label={`Subir pregunta ${faq.question}`}
                                                                        >
                                                                            <ArrowUp />
                                                                        </Button>
                                                                    </>
                                                                )}
                                                            </Form>
                                                            <Form
                                                                {...CatalogProductFaqController.move.form(
                                                                    [
                                                                        catalogProduct.id,
                                                                        faq.id,
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
                                                                                faqIndex ===
                                                                                    faqs.length -
                                                                                        1
                                                                            }
                                                                            aria-label={`Bajar pregunta ${faq.question}`}
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
                                                                aria-label={`Editar pregunta ${faq.question}`}
                                                                onClick={() =>
                                                                    setEditingFaqId(
                                                                        faq.id,
                                                                    )
                                                                }
                                                            >
                                                                <Pencil />
                                                            </Button>
                                                            <Form
                                                                {...CatalogProductFaqController.destroy.form(
                                                                    [
                                                                        catalogProduct.id,
                                                                        faq.id,
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
                                                                        aria-label={`Eliminar pregunta ${faq.question}`}
                                                                    >
                                                                        <Trash2 />
                                                                    </Button>
                                                                )}
                                                            </Form>
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">
                                            Todavia no hay preguntas
                                            frecuentes.
                                        </p>
                                    )}

                                    <Form
                                        {...CatalogProductFaqController.store.form(
                                            catalogProduct.id,
                                        )}
                                        resetOnSuccess
                                        className="space-y-3 border-t pt-6"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="question">
                                                        Pregunta
                                                    </Label>
                                                    <Input
                                                        id="question"
                                                        name="question"
                                                        required
                                                        placeholder="¿Cuanto tarda el pedido?"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.question
                                                        }
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="answer">
                                                        Respuesta
                                                    </Label>
                                                    <Textarea
                                                        id="answer"
                                                        name="answer"
                                                        required
                                                        rows={3}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.answer
                                                        }
                                                    />
                                                </div>
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                >
                                                    Agregar pregunta
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        </section>

                        <section id="reviews" className="scroll-mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Reseñas</CardTitle>
                                    <CardDescription>
                                        Testimonios que se muestran en un
                                        carrusel en la pagina del producto.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    {reviews.length > 0 ? (
                                        <div className="space-y-3">
                                            {reviews.map(
                                                (review, reviewIndex) =>
                                                    editingReviewId ===
                                                    review.id ? (
                                                        <Form
                                                            key={review.id}
                                                            {...CatalogProductReviewController.update.form(
                                                                [
                                                                    catalogProduct.id,
                                                                    review.id,
                                                                ],
                                                            )}
                                                            onSuccess={() =>
                                                                setEditingReviewId(
                                                                    null,
                                                                )
                                                            }
                                                            className="space-y-3 rounded-lg border p-4"
                                                        >
                                                            {({
                                                                processing,
                                                                errors,
                                                            }) => (
                                                                <>
                                                                    <div className="flex flex-wrap items-end gap-3">
                                                                        <div className="grid flex-1 gap-2">
                                                                            <Label
                                                                                htmlFor={`edit_author_${review.id}`}
                                                                            >
                                                                                Nombre
                                                                            </Label>
                                                                            <Input
                                                                                id={`edit_author_${review.id}`}
                                                                                name="author_name"
                                                                                required
                                                                                defaultValue={
                                                                                    review.author_name
                                                                                }
                                                                            />
                                                                            <InputError
                                                                                message={
                                                                                    errors.author_name
                                                                                }
                                                                            />
                                                                        </div>
                                                                        <div className="grid gap-2">
                                                                            <Label
                                                                                htmlFor={`edit_rating_${review.id}`}
                                                                            >
                                                                                Calificacion
                                                                            </Label>
                                                                            <RatingSelect
                                                                                id={`edit_rating_${review.id}`}
                                                                                name="rating"
                                                                                defaultValue={
                                                                                    review.rating
                                                                                }
                                                                            />
                                                                            <InputError
                                                                                message={
                                                                                    errors.rating
                                                                                }
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label
                                                                            htmlFor={`edit_comment_${review.id}`}
                                                                        >
                                                                            Comentario
                                                                        </Label>
                                                                        <Textarea
                                                                            id={`edit_comment_${review.id}`}
                                                                            name="comment"
                                                                            required
                                                                            rows={
                                                                                3
                                                                            }
                                                                            defaultValue={
                                                                                review.comment
                                                                            }
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.comment
                                                                            }
                                                                        />
                                                                    </div>
                                                                    <div className="flex gap-2">
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
                                                                                setEditingReviewId(
                                                                                    null,
                                                                                )
                                                                            }
                                                                        >
                                                                            <X />
                                                                        </Button>
                                                                    </div>
                                                                </>
                                                            )}
                                                        </Form>
                                                    ) : (
                                                        <div
                                                            key={review.id}
                                                            className="flex items-start justify-between gap-3 rounded-lg border p-4"
                                                        >
                                                            <div className="min-w-0 flex-1">
                                                                <div className="flex items-center gap-2">
                                                                    <p className="font-medium">
                                                                        {
                                                                            review.author_name
                                                                        }
                                                                    </p>
                                                                    <RatingStars
                                                                        rating={
                                                                            review.rating
                                                                        }
                                                                    />
                                                                </div>
                                                                <p className="mt-1 text-sm text-muted-foreground">
                                                                    {
                                                                        review.comment
                                                                    }
                                                                </p>
                                                            </div>
                                                            <div className="flex shrink-0 gap-1">
                                                                <Form
                                                                    {...CatalogProductReviewController.move.form(
                                                                        [
                                                                            catalogProduct.id,
                                                                            review.id,
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
                                                                                    reviewIndex ===
                                                                                        0
                                                                                }
                                                                                aria-label={`Subir reseña de ${review.author_name}`}
                                                                            >
                                                                                <ArrowUp />
                                                                            </Button>
                                                                        </>
                                                                    )}
                                                                </Form>
                                                                <Form
                                                                    {...CatalogProductReviewController.move.form(
                                                                        [
                                                                            catalogProduct.id,
                                                                            review.id,
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
                                                                                    reviewIndex ===
                                                                                        reviews.length -
                                                                                            1
                                                                                }
                                                                                aria-label={`Bajar reseña de ${review.author_name}`}
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
                                                                    aria-label={`Editar reseña de ${review.author_name}`}
                                                                    onClick={() =>
                                                                        setEditingReviewId(
                                                                            review.id,
                                                                        )
                                                                    }
                                                                >
                                                                    <Pencil />
                                                                </Button>
                                                                <Form
                                                                    {...CatalogProductReviewController.destroy.form(
                                                                        [
                                                                            catalogProduct.id,
                                                                            review.id,
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
                                                                            aria-label={`Eliminar reseña de ${review.author_name}`}
                                                                        >
                                                                            <Trash2 />
                                                                        </Button>
                                                                    )}
                                                                </Form>
                                                            </div>
                                                        </div>
                                                    ),
                                            )}
                                        </div>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">
                                            Todavia no hay reseñas.
                                        </p>
                                    )}

                                    <Form
                                        {...CatalogProductReviewController.store.form(
                                            catalogProduct.id,
                                        )}
                                        resetOnSuccess
                                        className="space-y-3 border-t pt-6"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="flex flex-wrap items-end gap-3">
                                                    <div className="grid flex-1 gap-2">
                                                        <Label htmlFor="author_name">
                                                            Nombre
                                                        </Label>
                                                        <Input
                                                            id="author_name"
                                                            name="author_name"
                                                            required
                                                            placeholder="Maria G."
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.author_name
                                                            }
                                                        />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label htmlFor="rating">
                                                            Calificacion
                                                        </Label>
                                                        <RatingSelect
                                                            id="rating"
                                                            name="rating"
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.rating
                                                            }
                                                        />
                                                    </div>
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="comment">
                                                        Comentario
                                                    </Label>
                                                    <Textarea
                                                        id="comment"
                                                        name="comment"
                                                        required
                                                        rows={3}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.comment
                                                        }
                                                    />
                                                </div>
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                >
                                                    Agregar reseña
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        </section>

                        <Button variant="outline" asChild>
                            <Link href={index()}>Volver al catalogo</Link>
                        </Button>
                    </div>

                    <SectionNav sections={sections} />
                </div>
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
