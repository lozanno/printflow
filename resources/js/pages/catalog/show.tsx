import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getCsrfToken, toUrl } from '@/lib/utils';
import { home } from '@/routes';
import { quote } from '@/routes/catalog';

type InputType = 'CHOICE' | 'NUMBER' | 'DIMENSIONS';

type ComponentOption = {
    value: string;
    label: string;
};

type ProductComponent = {
    code: string;
    label: string;
    input_type: InputType;
    is_required: boolean;
    options: ComponentOption[];
};

type CatalogProductDetail = {
    id: number;
    name: string;
    components: ProductComponent[];
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

function isDimensions(value: SelectionValue | undefined): value is Dimensions {
    return typeof value === 'object' && value !== null;
}

function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency,
    }).format(amount);
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

            fetch(toUrl(quote(catalogProduct.id)), {
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
    }, [selections, catalogProduct.id]);

    return (
        <>
            <Head title={catalogProduct.name} />

            <div className="min-h-screen bg-zinc-50 px-6 py-10">
                <div className="mx-auto max-w-5xl">
                    <Link
                        href={home()}
                        className="text-sm text-zinc-500 hover:text-zinc-700"
                    >
                        &larr; Volver al catalogo
                    </Link>

                    <h1 className="mt-2 text-3xl font-bold tracking-tight text-zinc-900">
                        {catalogProduct.name}
                    </h1>

                    <div className="mt-8 grid gap-8 lg:grid-cols-[1fr_340px]">
                        <div className="space-y-7 rounded-2xl bg-white p-6 shadow-sm">
                            {catalogProduct.components.map((component) => (
                                <div key={component.code}>
                                    <Label className="block text-sm font-semibold text-zinc-800">
                                        {component.label}
                                        {!component.is_required && (
                                            <span className="ml-1 font-normal text-zinc-400">
                                                (opcional)
                                            </span>
                                        )}
                                    </Label>

                                    {component.input_type === 'CHOICE' && (
                                        <div className="mt-3 flex flex-wrap gap-3">
                                            {component.options.map((option) => (
                                                <Button
                                                    key={option.value}
                                                    type="button"
                                                    variant={
                                                        selections[
                                                            component.code
                                                        ] === option.value
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                    onClick={() =>
                                                        updateSelection(
                                                            component.code,
                                                            option.value,
                                                        )
                                                    }
                                                >
                                                    {option.label}
                                                </Button>
                                            ))}
                                        </div>
                                    )}

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
                            ))}
                        </div>

                        <div className="h-fit rounded-2xl bg-zinc-900 p-6 text-white shadow-sm">
                            <h3 className="text-xl font-bold">Resumen</h3>

                            {quoteResult ? (
                                <div className="mt-6 space-y-3">
                                    <div className="flex justify-between text-sm text-zinc-400">
                                        <span>Precio base</span>
                                        <span>
                                            {formatMoney(
                                                quoteResult.base_price,
                                                quoteResult.currency,
                                            )}
                                        </span>
                                    </div>

                                    {quoteResult.modifiers.map(
                                        (modifier, index) => (
                                            <div
                                                key={index}
                                                className="flex justify-between text-sm text-zinc-400"
                                            >
                                                <span>{modifier.label}</span>
                                                <span>
                                                    {modifier.amount >= 0
                                                        ? '+'
                                                        : ''}
                                                    {formatMoney(
                                                        modifier.amount,
                                                        quoteResult.currency,
                                                    )}
                                                </span>
                                            </div>
                                        ),
                                    )}

                                    <div className="mt-4 rounded-xl bg-zinc-800 p-4">
                                        <p className="text-xs text-zinc-400 uppercase">
                                            Total
                                        </p>
                                        <p className="text-3xl font-bold">
                                            {formatMoney(
                                                quoteResult.total,
                                                quoteResult.currency,
                                            )}
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="mt-6 rounded-xl bg-zinc-800 p-4">
                                    <p className="text-sm text-zinc-400">
                                        {loading
                                            ? 'Calculando...'
                                            : (quoteError ??
                                              'Completa las opciones para ver tu precio.')}
                                    </p>
                                </div>
                            )}

                            <Button
                                className="mt-4 w-full"
                                variant="secondary"
                                disabled
                            >
                                Continuar al pedido
                            </Button>
                            <p className="mt-2 text-center text-xs text-zinc-500">
                                Muy pronto: pagar y hacer tu pedido desde aqui.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
