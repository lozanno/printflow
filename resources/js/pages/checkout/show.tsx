import { Head, Link, useForm } from '@inertiajs/react';
import { ImageOff } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { show } from '@/routes/catalog';
import { store } from '@/routes/catalog/orders';

type DeliveryType = 'PICKUP' | 'SHIP';
type PaymentMethod = 'card' | 'oxxo' | 'spei';
type SelectionValue = string | number | { width: number; height: number };
type Selections = Record<string, SelectionValue>;

type CheckoutProduct = {
    slug: string;
    name: string;
    image_url: string | null;
};

function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency,
    }).format(amount);
}

function ToggleOption<T extends string>({
    value,
    selected,
    onSelect,
    children,
}: {
    value: T;
    selected: T;
    onSelect: (value: T) => void;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={() => onSelect(value)}
            className={cn(
                'flex-1 rounded-lg border px-4 py-3 text-center text-sm font-medium transition',
                selected === value
                    ? 'border-[var(--shop-accent)] bg-zinc-50 text-zinc-900 ring-1 ring-[var(--shop-accent)]'
                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-400',
            )}
        >
            {children}
        </button>
    );
}

export default function CheckoutShow({
    catalogProduct,
    selections,
    selectionSummary,
    quote,
}: {
    catalogProduct: CheckoutProduct;
    selections: Selections;
    selectionSummary: string[];
    quote: { total: number; currency: string };
}) {
    const form = useForm({
        customer: { name: '', email: '', phone: '' },
        delivery_type: 'PICKUP' as DeliveryType,
        shipping: {
            recipient_name: '',
            phone: '',
            line1: '',
            line2: '',
            city: '',
            state: '',
            postal_code: '',
            country: 'MX',
        },
        payment_method: 'card' as PaymentMethod,
        selections,
    });

    function submit(event: React.FormEvent) {
        event.preventDefault();
        form.post(store(catalogProduct.slug).url);
    }

    return (
        <>
            <Head title={`Pedido - ${catalogProduct.name}`} />

            <div className="px-6 py-10 pb-24">
                <div className="mx-auto max-w-3xl">
                    <Link
                        href={show(catalogProduct.slug)}
                        className="text-sm text-zinc-500 hover:text-zinc-700"
                    >
                        &larr; Volver al producto
                    </Link>

                    <h1 className="mt-4 text-2xl font-bold tracking-tight text-[var(--shop-primary)]">
                        Confirma tu pedido
                    </h1>

                    <div className="mt-6 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm">
                        <div className="size-16 shrink-0 overflow-hidden rounded-lg bg-zinc-100">
                            {catalogProduct.image_url ? (
                                <img
                                    src={catalogProduct.image_url}
                                    alt={catalogProduct.name}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-full w-full items-center justify-center text-zinc-300">
                                    <ImageOff className="size-6" />
                                </div>
                            )}
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="font-semibold text-zinc-900">
                                {catalogProduct.name}
                            </p>
                            <p className="truncate text-sm text-zinc-500">
                                {selectionSummary.join(' · ')}
                            </p>
                        </div>
                        <p className="shrink-0 text-lg font-bold text-zinc-900">
                            {formatMoney(quote.total, quote.currency)}
                        </p>
                    </div>

                    <form onSubmit={submit} className="mt-6 space-y-6">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h2 className="text-sm font-semibold text-zinc-800">
                                Datos de contacto
                            </h2>
                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="customer_name">
                                        Nombre
                                    </Label>
                                    <Input
                                        id="customer_name"
                                        className="mt-1.5"
                                        value={form.data.customer.name}
                                        onChange={(event) =>
                                            form.setData('customer', {
                                                ...form.data.customer,
                                                name: event.target.value,
                                            })
                                        }
                                    />
                                    <InputError
                                        message={form.errors['customer.name']}
                                        className="mt-1"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="customer_email">
                                        Correo
                                    </Label>
                                    <Input
                                        id="customer_email"
                                        type="email"
                                        className="mt-1.5"
                                        value={form.data.customer.email}
                                        onChange={(event) =>
                                            form.setData('customer', {
                                                ...form.data.customer,
                                                email: event.target.value,
                                            })
                                        }
                                    />
                                    <InputError
                                        message={form.errors['customer.email']}
                                        className="mt-1"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="customer_phone">
                                        Telefono
                                    </Label>
                                    <Input
                                        id="customer_phone"
                                        className="mt-1.5"
                                        value={form.data.customer.phone}
                                        onChange={(event) =>
                                            form.setData('customer', {
                                                ...form.data.customer,
                                                phone: event.target.value,
                                            })
                                        }
                                    />
                                    <InputError
                                        message={form.errors['customer.phone']}
                                        className="mt-1"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h2 className="text-sm font-semibold text-zinc-800">
                                Entrega
                            </h2>
                            <div className="mt-4 flex gap-3">
                                <ToggleOption
                                    value="PICKUP"
                                    selected={form.data.delivery_type}
                                    onSelect={(value) =>
                                        form.setData('delivery_type', value)
                                    }
                                >
                                    Recoger en tienda
                                </ToggleOption>
                                <ToggleOption
                                    value="SHIP"
                                    selected={form.data.delivery_type}
                                    onSelect={(value) =>
                                        form.setData('delivery_type', value)
                                    }
                                >
                                    Envio a domicilio
                                </ToggleOption>
                            </div>

                            {form.data.delivery_type === 'SHIP' && (
                                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label htmlFor="recipient_name">
                                            Nombre de quien recibe
                                        </Label>
                                        <Input
                                            id="recipient_name"
                                            className="mt-1.5"
                                            value={
                                                form.data.shipping
                                                    .recipient_name
                                            }
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    recipient_name:
                                                        event.target.value,
                                                })
                                            }
                                        />
                                        <InputError
                                            message={
                                                form.errors[
                                                    'shipping.recipient_name'
                                                ]
                                            }
                                            className="mt-1"
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="shipping_phone">
                                            Telefono de contacto
                                        </Label>
                                        <Input
                                            id="shipping_phone"
                                            className="mt-1.5"
                                            value={form.data.shipping.phone}
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    phone: event.target.value,
                                                })
                                            }
                                        />
                                        <InputError
                                            message={
                                                form.errors['shipping.phone']
                                            }
                                            className="mt-1"
                                        />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label htmlFor="line1">Direccion</Label>
                                        <Input
                                            id="line1"
                                            className="mt-1.5"
                                            value={form.data.shipping.line1}
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    line1: event.target.value,
                                                })
                                            }
                                        />
                                        <InputError
                                            message={
                                                form.errors['shipping.line1']
                                            }
                                            className="mt-1"
                                        />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label htmlFor="line2">
                                            Referencias (opcional)
                                        </Label>
                                        <Input
                                            id="line2"
                                            className="mt-1.5"
                                            value={form.data.shipping.line2}
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    line2: event.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="city">Ciudad</Label>
                                        <Input
                                            id="city"
                                            className="mt-1.5"
                                            value={form.data.shipping.city}
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    city: event.target.value,
                                                })
                                            }
                                        />
                                        <InputError
                                            message={
                                                form.errors['shipping.city']
                                            }
                                            className="mt-1"
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="state">Estado</Label>
                                        <Input
                                            id="state"
                                            className="mt-1.5"
                                            value={form.data.shipping.state}
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    state: event.target.value,
                                                })
                                            }
                                        />
                                        <InputError
                                            message={
                                                form.errors['shipping.state']
                                            }
                                            className="mt-1"
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="postal_code">
                                            Codigo postal
                                        </Label>
                                        <Input
                                            id="postal_code"
                                            className="mt-1.5"
                                            value={
                                                form.data.shipping.postal_code
                                            }
                                            onChange={(event) =>
                                                form.setData('shipping', {
                                                    ...form.data.shipping,
                                                    postal_code:
                                                        event.target.value,
                                                })
                                            }
                                        />
                                        <InputError
                                            message={
                                                form.errors[
                                                    'shipping.postal_code'
                                                ]
                                            }
                                            className="mt-1"
                                        />
                                    </div>
                                </div>
                            )}
                        </div>

                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <h2 className="text-sm font-semibold text-zinc-800">
                                Pago
                            </h2>
                            <div className="mt-4 flex gap-3">
                                <ToggleOption
                                    value="card"
                                    selected={form.data.payment_method}
                                    onSelect={(value) =>
                                        form.setData('payment_method', value)
                                    }
                                >
                                    Tarjeta
                                </ToggleOption>
                                <ToggleOption
                                    value="oxxo"
                                    selected={form.data.payment_method}
                                    onSelect={(value) =>
                                        form.setData('payment_method', value)
                                    }
                                >
                                    OXXO
                                </ToggleOption>
                                <ToggleOption
                                    value="spei"
                                    selected={form.data.payment_method}
                                    onSelect={(value) =>
                                        form.setData('payment_method', value)
                                    }
                                >
                                    SPEI
                                </ToggleOption>
                            </div>
                            <p className="mt-3 text-xs text-zinc-400">
                                Demo: no se realiza ningun cobro real.
                            </p>
                        </div>

                        <InputError message={form.errors.selections} />

                        <Button
                            type="submit"
                            disabled={form.processing}
                            className="w-full bg-[var(--shop-accent)] text-white hover:opacity-90"
                        >
                            {form.processing
                                ? 'Procesando...'
                                : `Pagar ${formatMoney(quote.total, quote.currency)}`}
                        </Button>
                    </form>
                </div>
            </div>
        </>
    );
}
