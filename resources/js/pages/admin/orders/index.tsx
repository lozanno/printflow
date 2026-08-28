import { Head, router } from '@inertiajs/react';
import Heading from '@/components/heading';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    formatDate,
    formatDateOnly,
    stageBadgeClasses,
    stageLabels,
    stageRowClasses,
} from '@/lib/orders';
import { cn } from '@/lib/utils';
import { index, show } from '@/routes/admin/orders';
import type { AdminOrder } from '@/types/admin';

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
                                <TableHead>ID</TableHead>
                                <TableHead>Cliente</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Fecha de pedido</TableHead>
                                <TableHead>Fecha de entrega</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {orders.map((order) => (
                                <TableRow
                                    key={order.id}
                                    className={cn(
                                        'cursor-pointer',
                                        order.production_stage &&
                                            stageRowClasses(
                                                order.production_stage,
                                                order.needs_sales_attention,
                                            ),
                                    )}
                                    onClick={() =>
                                        router.visit(show(order.id).url)
                                    }
                                >
                                    <TableCell className="font-medium">
                                        <span className="flex items-center gap-2">
                                            {order.is_urgent && (
                                                <span
                                                    className="inline-block size-2.5 shrink-0 rounded-full bg-red-500"
                                                    title="Urgente"
                                                />
                                            )}
                                            #{order.id}
                                        </span>
                                    </TableCell>
                                    <TableCell>{order.customer_name}</TableCell>
                                    <TableCell>
                                        {order.production_stage === null ? (
                                            <span className="text-sm text-muted-foreground">
                                                -
                                            </span>
                                        ) : (
                                            <span
                                                className={cn(
                                                    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium',
                                                    stageBadgeClasses(
                                                        order.production_stage,
                                                        order.needs_sales_attention,
                                                    ),
                                                )}
                                            >
                                                {order.needs_sales_attention
                                                    ? 'Necesita atencion'
                                                    : stageLabels[
                                                          order.production_stage
                                                      ]}
                                            </span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-sm text-muted-foreground">
                                        {formatDate(order.created_at)}
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        {formatDateOnly(
                                            order.estimated_delivery_date,
                                        )}
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
