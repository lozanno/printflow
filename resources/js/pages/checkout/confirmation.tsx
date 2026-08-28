import { Head, Link } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

type ConfirmedOrder = {
    id: number;
    status: string;
    total: number;
    currency: string;
    customer_name: string;
    delivery_type: 'PICKUP' | 'SHIP';
    product_names: string[];
};

function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency,
    }).format(amount);
}

export default function CheckoutConfirmation({
    order,
}: {
    order: ConfirmedOrder;
}) {
    return (
        <>
            <Head title={`Pedido #${order.id} confirmado`} />

            <div className="px-6 py-16">
                <div className="mx-auto max-w-md text-center">
                    <CheckCircle2 className="mx-auto size-14 text-emerald-500" />

                    <h1 className="mt-4 text-2xl font-bold tracking-tight text-[var(--shop-primary)]">
                        Gracias, {order.customer_name.split(' ')[0]}
                    </h1>
                    <p className="mt-2 text-zinc-600">
                        Tu pedido #{order.id} fue confirmado y el pago quedo
                        registrado.
                    </p>

                    <div className="mt-6 space-y-3 rounded-2xl bg-white p-6 text-left shadow-sm">
                        <div className="flex justify-between text-sm">
                            <span className="text-zinc-500">Productos</span>
                            <span className="font-medium text-zinc-900">
                                {order.product_names.join(', ')}
                            </span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span className="text-zinc-500">Entrega</span>
                            <span className="font-medium text-zinc-900">
                                {order.delivery_type === 'PICKUP'
                                    ? 'Recoger en tienda'
                                    : 'Envio a domicilio'}
                            </span>
                        </div>
                        <div className="flex justify-between border-t border-zinc-100 pt-3 text-base">
                            <span className="font-semibold text-zinc-800">
                                Total pagado
                            </span>
                            <span className="font-bold text-zinc-900">
                                {formatMoney(order.total, order.currency)}
                            </span>
                        </div>
                    </div>

                    <Button asChild className="mt-8">
                        <Link href={home()}>Volver al catalogo</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
