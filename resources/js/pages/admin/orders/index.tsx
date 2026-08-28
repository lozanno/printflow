import { Head, Link, router, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
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
import {
    deliveryLabels,
    formatDate,
    formatMoney,
    stageLabels,
    stageVariants,
    statusLabels,
    statusVariants,
} from '@/lib/orders';
import { index, show } from '@/routes/admin/orders';
import { update as updateProductionStage } from '@/routes/admin/orders/production-stage';
import { update as updateQualityCheck } from '@/routes/admin/orders/quality-check';
import type { AdminOrder, ProductionStage } from '@/types/admin';

function handleStageChange(orderId: number, stage: string) {
    router.patch(
        updateProductionStage(orderId).url,
        { production_stage: stage },
        {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    errors.production_stage ??
                        'No se pudo actualizar la etapa.',
                ),
        },
    );
}

function handleQualityCheckChange(orderId: number, passed: boolean) {
    router.patch(
        updateQualityCheck(orderId).url,
        { passed },
        { preserveScroll: true },
    );
}

export default function OrdersIndex({ orders }: { orders: AdminOrder[] }) {
    const { auth } = usePage().props;
    const canAdvanceProduction =
        auth.user.role === 'ADMIN' || auth.user.role === 'PRODUCCION';
    const canCheckQuality =
        auth.user.role === 'ADMIN' || auth.user.role === 'CALIDAD';

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
                                <TableHead>Produccion</TableHead>
                                <TableHead>Calidad</TableHead>
                                <TableHead>Fecha</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {orders.map((order) => (
                                <TableRow key={order.id}>
                                    <TableCell>
                                        <Link
                                            href={show(order.id)}
                                            className="font-medium hover:underline"
                                        >
                                            {order.customer_name}
                                        </Link>
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
                                    <TableCell>
                                        {order.production_stage === null ? (
                                            <span className="text-sm text-muted-foreground">
                                                -
                                            </span>
                                        ) : canAdvanceProduction ? (
                                            <Select
                                                value={order.production_stage}
                                                onValueChange={(value) =>
                                                    handleStageChange(
                                                        order.id,
                                                        value,
                                                    )
                                                }
                                            >
                                                <SelectTrigger size="sm">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {(
                                                        Object.keys(
                                                            stageLabels,
                                                        ) as ProductionStage[]
                                                    ).map((stage) => (
                                                        <SelectItem
                                                            key={stage}
                                                            value={stage}
                                                        >
                                                            {stageLabels[stage]}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        ) : (
                                            <Badge
                                                variant={
                                                    stageVariants[
                                                        order.production_stage
                                                    ]
                                                }
                                            >
                                                {
                                                    stageLabels[
                                                        order.production_stage
                                                    ]
                                                }
                                            </Badge>
                                        )}
                                        {order.production_stage_updated_by && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {
                                                    order.production_stage_updated_by
                                                }{' '}
                                                ·{' '}
                                                {formatDate(
                                                    order.production_stage_updated_at,
                                                )}
                                            </p>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {canCheckQuality ? (
                                            <Checkbox
                                                checked={order.quality_checked}
                                                onCheckedChange={(checked) =>
                                                    handleQualityCheckChange(
                                                        order.id,
                                                        checked === true,
                                                    )
                                                }
                                                aria-label="Control de calidad aprobado"
                                            />
                                        ) : (
                                            <Badge
                                                variant={
                                                    order.quality_checked
                                                        ? 'default'
                                                        : 'outline'
                                                }
                                            >
                                                {order.quality_checked
                                                    ? 'Aprobado'
                                                    : 'Pendiente'}
                                            </Badge>
                                        )}
                                        {order.quality_checked_by && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {order.quality_checked_by} ·{' '}
                                                {formatDate(
                                                    order.quality_checked_at,
                                                )}
                                            </p>
                                        )}
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
