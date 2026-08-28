import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/admin/orders';
import type { AdminOrder, DeliveryType, OrderStatus } from '@/types/admin';

const statusLabels: Record<OrderStatus, string> = {
    PENDING_PAYMENT: 'Pendiente de pago',
    PAID: 'Pagado',
    COMPLETED: 'Completado',
    CANCELLED: 'Cancelado',
};

const statusVariants: Record<
    OrderStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    PENDING_PAYMENT: 'outline',
    PAID: 'default',
    COMPLETED: 'secondary',
    CANCELLED: 'destructive',
};

const deliveryLabels: Record<DeliveryType, string> = {
    PICKUP: 'Recoger en tienda',
    SHIP: 'Envio a domicilio',
};

function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency,
    }).format(amount);
}

function formatDate(iso: string | null): string {
    if (!iso) {
        return '-';
    }

    return new Intl.DateTimeFormat('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));
}

export default function OrdersIndex({ orders }: { orders: AdminOrder[] }) {
    return (
        <>
            <Head title="Pedidos" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Pedidos"
                    description="Ventas realizadas a traves del catalogo publico."
                />

                {orders.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavia no hay pedidos.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Cliente</TableHead>
                                <TableHead>Producto</TableHead>
                                <TableHead>Entrega</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead>Fecha</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {orders.map((order) => (
                                <TableRow key={order.id}>
                                    <TableCell>
                                        <div className="font-medium">
                                            {order.customer_name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {order.customer_email}
                                        </div>
                                    </TableCell>
                                    <TableCell>{order.product_names}</TableCell>
                                    <TableCell>
                                        {deliveryLabels[order.delivery_type]}
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {formatMoney(
                                            order.total,
                                            order.currency,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                statusVariants[order.status]
                                            }
                                        >
                                            {statusLabels[order.status]}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-sm text-muted-foreground">
                                        {formatDate(order.created_at)}
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

OrdersIndex.layout = {
    breadcrumbs: [{ title: 'Pedidos', href: index() }],
};
