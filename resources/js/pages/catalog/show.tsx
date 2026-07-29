import { Head, Link } from '@inertiajs/react';
import { ChevronDown, ImageOff } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn, getCsrfToken, toUrl } from '@/lib/utils';
import { home } from '@/routes';
import { quote, tierTable } from '@/routes/catalog';

type InputType = 'CHOICE' | 'NUMBER' | 'DIMENSIONS';
type PricingStrategy = 'PER_UNIT_TIERED' | 'PER_AREA' | 'PER_AREA_WITH_SETUP';

type ComponentOption = {
    value: string;
    label: string;
    image_url: string | null;
};

type ProductComponent = {
    code: string;
    label: string;
    input_type: InputType;
    is_required: boolean;
    options: ComponentOption[];
};

type PricingTier = {
    min_quantity: number;
    max_quantity: number | null;
    unit_price: number;
    total: number;
};

type CatalogProductDetail = {
    id: number;
    slug: string;
    name: string;
    image_url: string | null;
    description: string | null;
    currency: string;
    pricing_strategy: PricingStrategy;
    components: ProductComponent[];
    pricing_tiers: PricingTier[];
};

type QuoteModifier = {
    label: string;
    amount: number;
};

type QuoteResponse = {
    base_price: number;
    modifiers: QuoteModifier[];
    total: number;
    currency: string;
};

type Dimensions = { width?: number; height?: number };
type SelectionValue = string | number | Dimensions;
type Selections = Record<string, SelectionValue>;

const VISIBLE_ITEM_LIMIT = 5;

function isDimensions(value: SelectionValue | undefined): value is Dimensions {
    return typeof value === 'object' && value !== null;
}

function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency,
    }).format(amount);
}

function describeSelection(
    component: ProductComponent,
    value: SelectionValue,
): string | null {
    if (isDimensions(value)) {
        if (!value.width || !value.height) {
            return null;
        }

        return `${component.label}: ${value.width}m x ${value.height}m`;
    }

    if (component.input_type === 'CHOICE') {
        const option = component.options.find((o) => o.value === value);

        return option ? `${component.label}: ${option.label}` : null;
    }

    return value === '' ? null : `${component.label}: ${value}`;
}

function ShowMoreToggle({
    hiddenCount,
    expanded,
    onToggle,
}: {
    hiddenCount: number;
    expanded: boolean;
    onToggle: () => void;
}) {
    if (hiddenCount <= 0) {
        return null;
    }

    return (
        <button
            type="button"
            onClick={onToggle}
            className="flex w-full items-center justify-between border-t border-zinc-100 px-4 py-3 text-sm text-zinc-500 hover:text-zinc-700"
        >
            {expanded ? 'Mostrar menos' : 'Mostrar mas'}
            <ChevronDown
                className={cn(
                    'size-4 transition-transform',
                    expanded && 'rotate-180',
                )}
            />
        </button>
    );
}

function TableStyleOptions({
    component,
    selected,
    onSelect,
}: {
    component: ProductComponent;
    selected: SelectionValue | undefined;
    onSelect: (value: string) => void;
}) {
    const [expanded, setExpanded] = useState(false);
    const visible = expanded
        ? component.options
        : component.options.slice(0, VISIBLE_ITEM_LIMIT);
    const hiddenCount = component.options.length - visible.length;

    return (
        <div className="mt-3 overflow-hidden rounded-lg border border-zinc-200">
            <div className="bg-zinc-50 px-4 py-2 text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                {component.label}
            </div>
            {visible.map((option) => (
                <button
                    key={option.value}
                    type="button"
                    onClick={() => onSelect(option.value)}
                    className={cn(
                        'flex w-full items-center justify-between border-t border-zinc-100 px-4 py-3 text-left text-sm font-medium transition',
                        selected === option.value
                            ? 'border-zinc-900 bg-zinc-50 text-zinc-900 ring-1 ring-inset ring-zinc-900'
                            : 'text-zinc-700 hover:bg-zinc-50',
                    )}
                >
                    {option.label}
                </button>
            ))}
            <ShowMoreToggle
                hiddenCount={hiddenCount}
                expanded={expanded}
                onToggle={() => setExpanded((current) => !current)}
            />
        </div>
    );
}

function IllustratedOptions({
    component,
    selected,
    onSelect,
}: {
    component: ProductComponent;
    selected: SelectionValue | undefined;
    onSelect: (value: string) => void;
}) {
    const [expanded, setExpanded] = useState(false);
    const visible = expanded
        ? component.options
        : component.options.slice(0, VISIBLE_ITEM_LIMIT);
    const hiddenCount = component.options.length - visible.length;

    return (
        <div className="mt-3">
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                {visible.map((option) => (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => onSelect(option.value)}
                        className={cn(
                            'flex flex-col items-center gap-2 rounded-xl border p-3 text-center transition',
                            selected === option.value
                                ? 'border-zinc-900 ring-1 ring-zinc-900'
                                : 'border-zinc-200 hover:border-zinc-400',
                        )}
                    >
                        {option.image_url ? (
                            <img
                                src={option.image_url}
                                alt=""
                                className="h-16 w-16 object-contain"
                            />
                        ) : (
                            <div className="flex h-16 w-16 items-center justify-center rounded bg-zinc-50 text-zinc-300">
                                <ImageOff className="size-6" />
                            </div>
                        )}
                        <span className="text-sm font-medium text-zinc-800">
                            {option.label}
                        </span>
                    </button>
                ))}
            </div>
            {hiddenCount > 0 && (
                <button
                    type="button"
                    onClick={() => setExpanded((current) => !current)}
                    className="mt-2 flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700"
                >
                    {expanded ? 'Mostrar menos' : 'Mostrar mas'}
                    <ChevronDown
                        className={cn(
                            'size-4 transition-transform',
                            expanded && 'rotate-180',
                        )}
                    />
                </button>
            )}
        </div>
    );
}

function QuantityTable({
    tiers,
    currency,
    selected,
    onSelect,
}: {
    tiers: PricingTier[];
    currency: string;
    selected: number | undefined;
    onSelect: (quantity: number) => void;
}) {
    const [expanded, setExpanded] = useState(false);
    const visible = expanded ? tiers : tiers.slice(0, VISIBLE_ITEM_LIMIT);
    const hiddenCount = tiers.length - visible.length;

    return (
        <div>
            <Label className="block text-sm font-semibold text-zinc-800">
                Cantidad
            </Label>

            <div className="mt-3 overflow-hidden rounded-lg border border-zinc-200">
                <div className="grid grid-cols-3 bg-zinc-50 px-4 py-2 text-xs font-semibold tracking-wide text-zinc-400 uppercase">
                    <span>Cantidad</span>
                    <span className="text-right">Precio unitario</span>
                    <span className="text-right">Total</span>
                </div>
                {visible.map((tier) => (
                    <button
                        key={tier.min_quantity}
                        type="button"
                        onClick={() => onSelect(tier.min_quantity)}
                        className={cn(
                            'grid w-full grid-cols-3 items-center border-t border-zinc-100 px-4 py-3 text-left text-sm transition',
                            selected === tier.min_quantity
                                ? 'border-zinc-900 bg-zinc-50 ring-1 ring-inset ring-zinc-900'
                                : 'hover:bg-zinc-50',
                        )}
                    >
                        <span className="font-medium text-zinc-800">
                            {tier.min_quantity} unidades
                        </span>
                        <span className="text-right font-semibold text-zinc-900">
                            {formatMoney(tier.unit_price, currency)}
                        </span>
                        <span className="text-right font-semibold text-zinc-900">
                            {formatMoney(tier.total, currency)}
                        </span>
                    </button>
                ))}
                <ShowMoreToggle
                    hiddenCount={hiddenCount}
                    expanded={expanded}
                    onToggle={() => setExpanded((current) => !current)}
                />
            </div>
        </div>
    );
}

export default function CatalogShow({
    catalogProduct,
}: {
    catalogProduct: CatalogProductDetail;
}) {
    const [selections, setSelections] = useState<Selections>({});
    const [quoteResult, setQuoteResult] = useState<QuoteResponse | null>(null);
    const [quoteError, setQuoteError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [tiers, setTiers] = useState<PricingTier[]>(
        catalogProduct.pricing_tiers,
    );

    function updateSelection(code: string, value: SelectionValue) {
        setSelections((current) => ({ ...current, [code]: value }));
    }

    function updateDimension(
        code: string,
        axis: keyof Dimensions,
        value: number,
    ) {
        setSelections((current) => {
            const existing = current[code];
            const dimensions = isDimensions(existing) ? existing : {};

            return { ...current, [code]: { ...dimensions, [axis]: value } };
        });
    }

    useEffect(() => {
        if (Object.keys(selections).length === 0) {
            return;
        }

        const controller = new AbortController();

        const timeout = setTimeout(() => {
            setLoading(true);

            fetch(toUrl(quote(catalogProduct.slug)), {
                method: 'POST',
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ selections }),
            })
                .then(async (response) => {
                    if (!response.ok) {
                        setQuoteResult(null);
                        setQuoteError(
                            response.status === 422
                                ? 'Completa las opciones requeridas para ver el precio.'
                                : 'No se pudo calcular el precio. Intenta de nuevo.',
                        );

                        return;
                    }

                    setQuoteResult((await response.json()) as QuoteResponse);
                    setQuoteError(null);
                })
                .catch((error: unknown) => {
                    if (
                        error instanceof DOMException &&
                        error.name === 'AbortError'
                    ) {
                        return;
                    }

                    setQuoteResult(null);
                    setQuoteError(
                        'No se pudo calcular el precio. Intenta de nuevo.',
                    );
                })
                .finally(() => setLoading(false));
        }, 300);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [selections, catalogProduct.slug]);

    useEffect(() => {
        if (
            catalogProduct.pricing_strategy !== 'PER_UNIT_TIERED' ||
            Object.keys(selections).length === 0
        ) {
            return;
        }

        const controller = new AbortController();

        const timeout = setTimeout(() => {
            fetch(toUrl(tierTable(catalogProduct.slug)), {
                method: 'POST',
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ selections }),
            })
                .then(async (response) => {
                    if (!response.ok) {
                        return;
                    }

                    const data = (await response.json()) as {
                        tiers: PricingTier[];
                    };

                    setTiers(data.tiers);
                })
                .catch((error: unknown) => {
                    if (
                        error instanceof DOMException &&
                        error.name === 'AbortError'
                    ) {
                        return;
                    }
                });
        }, 300);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [selections, catalogProduct.slug, catalogProduct.pricing_strategy]);

    const quantitySelected =
        typeof selections.quantity === 'number'
            ? selections.quantity
            : undefined;

    const selectionSummary = [
        ...(quantitySelected !== undefined
            ? [`Cantidad: ${quantitySelected} unidades`]
            : []),
        ...catalogProduct.components
            .map((component) => {
                const value = selections[component.code];

                return value === undefined
                    ? null
                    : describeSelection(component, value);
            })
            .filter((label): label is string => label !== null),
    ];

    const priceLabel = quoteResult
        ? formatMoney(quoteResult.total, quoteResult.currency)
        : (quoteError ??
          (loading ? 'Calculando...' : 'Completa las opciones para ver tu precio'));

    return (
        <>
            <Head title={catalogProduct.name} />

            <div className="min-h-screen bg-zinc-50 px-6 pt-10 pb-28">
                <div className="mx-auto max-w-3xl">
                    <Link
                        href={home()}
                        className="text-sm text-zinc-500 hover:text-zinc-700"
                    >
                        &larr; Volver al catalogo
                    </Link>

                    <div className="mt-4 overflow-hidden rounded-2xl bg-zinc-100">
                        <div className="aspect-16/7 w-full">
                            {catalogProduct.image_url ? (
                                <img
                                    src={catalogProduct.image_url}
                                    alt={catalogProduct.name}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-full w-full items-center justify-center text-zinc-300">
                                    <ImageOff className="size-12" />
                                </div>
                            )}
                        </div>
                    </div>

                    <h1 className="mt-6 text-3xl font-bold tracking-tight text-zinc-900">
                        {catalogProduct.name}
                    </h1>

                    {catalogProduct.description && (
                        <p className="mt-2 whitespace-pre-line text-zinc-600">
                            {catalogProduct.description}
                        </p>
                    )}

                    <div className="mt-8 space-y-7 rounded-2xl bg-white p-6 shadow-sm">
                        {catalogProduct.components.map((component) => {
                            const hasIllustratedOptions =
                                component.input_type === 'CHOICE' &&
                                component.options.some((o) => o.image_url);

                            return (
                                <div key={component.code}>
                                    {(component.input_type !== 'CHOICE' ||
                                        hasIllustratedOptions) && (
                                        <Label className="block text-sm font-semibold text-zinc-800">
                                            {component.label}
                                            {!component.is_required && (
                                                <span className="ml-1 font-normal text-zinc-400">
                                                    (opcional)
                                                </span>
                                            )}
                                        </Label>
                                    )}

                                    {component.input_type === 'CHOICE' &&
                                        (hasIllustratedOptions ? (
                                            <IllustratedOptions
                                                component={component}
                                                selected={
                                                    selections[component.code]
                                                }
                                                onSelect={(value) =>
                                                    updateSelection(
                                                        component.code,
                                                        value,
                                                    )
                                                }
                                            />
                                        ) : (
                                            <TableStyleOptions
                                                component={component}
                                                selected={
                                                    selections[component.code]
                                                }
                                                onSelect={(value) =>
                                                    updateSelection(
                                                        component.code,
                                                        value,
                                                    )
                                                }
                                            />
                                        ))}

                                    {component.input_type === 'NUMBER' && (
                                        <Input
                                            type="number"
                                            className="mt-3 max-w-40"
                                            onChange={(event) =>
                                                updateSelection(
                                                    component.code,
                                                    Number(event.target.value),
                                                )
                                            }
                                        />
                                    )}

                                    {component.input_type === 'DIMENSIONS' && (
                                        <div className="mt-3 flex max-w-72 gap-3">
                                            <div className="flex-1">
                                                <Label className="text-xs text-zinc-500">
                                                    Ancho (m)
                                                </Label>
                                                <Input
                                                    type="number"
                                                    step="0.01"
                                                    onChange={(event) =>
                                                        updateDimension(
                                                            component.code,
                                                            'width',
                                                            Number(
                                                                event.target
                                                                    .value,
                                                            ),
                                                        )
                                                    }
                                                />
                                            </div>
                                            <div className="flex-1">
                                                <Label className="text-xs text-zinc-500">
                                                    Alto (m)
                                                </Label>
                                                <Input
                                                    type="number"
                                                    step="0.01"
                                                    onChange={(event) =>
                                                        updateDimension(
                                                            component.code,
                                                            'height',
                                                            Number(
                                                                event.target
                                                                    .value,
                                                            ),
                                                        )
                                                    }
                                                />
                                            </div>
                                        </div>
                                    )}
                                </div>
                            );
                        })}

                        {catalogProduct.pricing_strategy ===
                            'PER_UNIT_TIERED' &&
                            tiers.length > 0 && (
                                <QuantityTable
                                    tiers={tiers}
                                    currency={
                                        quoteResult?.currency ??
                                        catalogProduct.currency
                                    }
                                    selected={quantitySelected}
                                    onSelect={(quantity) =>
                                        updateSelection('quantity', quantity)
                                    }
                                />
                            )}
                    </div>

                    <div className="mt-6 rounded-2xl bg-white p-6 shadow-sm">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm text-zinc-500">
                                    {catalogProduct.name}
                                    {quantitySelected !== undefined &&
                                        ` — ${quantitySelected} unidades`}
                                </p>
                                <p className="text-2xl font-bold text-zinc-900">
                                    {priceLabel}
                                </p>
                                <p className="mt-1 text-xs text-zinc-400">
                                    Todos los precios incluyen IVA.
                                </p>
                            </div>

                            <Button
                                variant="default"
                                disabled
                                className="shrink-0"
                            >
                                Agregar al carrito
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div className="fixed inset-x-0 bottom-0 border-t border-zinc-800 bg-zinc-900 text-white shadow-[0_-4px_16px_rgba(0,0,0,0.2)]">
                <div className="mx-auto flex max-w-3xl flex-wrap items-center gap-4 px-6 py-4">
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm text-zinc-400">
                            {selectionSummary.length > 0
                                ? `${catalogProduct.name} — ${selectionSummary.join(' · ')}`
                                : catalogProduct.name}
                        </p>
                        <p className="text-xl font-bold">{priceLabel}</p>
                    </div>

                    <Button variant="secondary" disabled className="shrink-0">
                        Continuar al pedido
                    </Button>
                </div>
            </div>
        </>
    );
}
