import { Head, router, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2, MessageSquare, PackageCheck } from 'lucide-react';
import { toast } from 'sonner';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import {
    deliveryLabels,
    formatDate,
    formatMoney,
    productionStages,
    stageLabels,
    statusLabels,
    statusVariants,
} from '@/lib/orders';
import { cn } from '@/lib/utils';
import { index } from '@/routes/admin/orders';
import { store as storeNote } from '@/routes/admin/orders/notes';
import { update as updateProductionStage } from '@/routes/admin/orders/production-stage';
import { update as updateQualityCheck } from '@/routes/admin/orders/quality-check';
import type { AdminOrderDetail, OrderTimelineEvent } from '@/types/admin';

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

function Pipeline({
    current,
}: {
    current: AdminOrderDetail['production_stage'];
}) {
    const currentIndex = current ? productionStages.indexOf(current) : -1;

    return (
        <div className="flex items-center">
            {productionStages.map((stage, index) => {
                const isDone = index < currentIndex;
                const isCurrent = index === currentIndex;

                return (
                    <div
                        key={stage}
                        className="flex flex-1 flex-col items-center last:flex-none"
                    >
                        <div className="flex w-full items-center">
                            <div
                                className={cn(
                                    'flex size-7 shrink-0 items-center justify-center rounded-full border text-xs font-semibold',
                                    isDone &&
                                        'border-primary bg-primary text-primary-foreground',
                                    isCurrent && 'border-primary text-primary',
                                    !isDone &&
                                        !isCurrent &&
                                        'border-muted-foreground/30 text-muted-foreground',
                                )}
                            >
                                {isDone ? (
                                    <CheckCircle2 className="size-4" />
                                ) : (
                                    index + 1
                                )}
                            </div>
                            {index < productionStages.length - 1 && (
                                <div
                                    className={cn(
                                        'h-px flex-1',
                                        isDone
                                            ? 'bg-primary'
                                            : 'bg-muted-foreground/30',
                                    )}
                                />
                            )}
                        </div>
                        <span
                            className={cn(
                                'mt-2 text-center text-xs',
                                isCurrent
                                    ? 'font-semibold text-foreground'
                                    : 'text-muted-foreground',
                            )}
                        >
                            {stageLabels[stage]}
                        </span>
                    </div>
                );
            })}
        </div>
    );
}

function TimelineEntry({ event }: { event: OrderTimelineEvent }) {
    if (event.type === 'note') {
        return (
            <div className="flex gap-3">
                <MessageSquare className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                <div>
                    <p className="text-sm whitespace-pre-line">{event.body}</p>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        {event.user_name ?? 'Alguien'} · {formatDate(event.at)}
                    </p>
                </div>
            </div>
        );
    }

    if (event.type === 'quality_check') {
        return (
            <div className="flex gap-3">
                <PackageCheck className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                <div>
                    <p className="text-sm">Aprobo el control de calidad.</p>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        {event.user_name ?? 'Alguien'} · {formatDate(event.at)}
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="flex gap-3">
            <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
            <div>
                <p className="text-sm">
                    Movio el pedido{' '}
                    {event.from_stage
                        ? `de ${stageLabels[event.from_stage]} `
                        : ''}
                    a {stageLabels[event.to_stage]}.
                </p>
                <p className="mt-0.5 text-xs text-muted-foreground">
                    {event.user_name ?? 'Alguien'} · {formatDate(event.at)}
                </p>
            </div>
        </div>
    );
}

export default function OrderShow({
    order,
    events,
}: {
    order: AdminOrderDetail;
    events: OrderTimelineEvent[];
}) {
    const { auth } = usePage().props;
    const canAdvanceProduction =
        auth.user.role === 'ADMIN' || auth.user.role === 'PRODUCCION';
    const canCheckQuality =
        auth.user.role === 'ADMIN' || auth.user.role === 'CALIDAD';

    const noteForm = useForm({ body: '' });

    function submitNote(event: React.FormEvent) {
        event.preventDefault();
        noteForm.post(storeNote(order.id).url, {
            preserveScroll: true,
            onSuccess: () => noteForm.reset('body'),
        });
    }

    return (
        <>
            <Head title={`Pedido #${order.id}`} />

            <div className="max-w-3xl space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={`Pedido #${order.id}`}
                        description={`${order.customer_name} · ${order.customer_email}`}
                    />
                    <Badge variant={statusVariants[order.status]}>
                        {statusLabels[order.status]}
                    </Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Detalles</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Producto
                            </span>
                            <span>{order.product_names}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Total</span>
                            <span className="font-medium">
                                {formatMoney(order.total, order.currency)}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Entrega
                            </span>
                            <span>{deliveryLabels[order.delivery_type]}</span>
                        </div>
                        {order.shipping_address && (
                            <div className="flex justify-between gap-4">
                                <span className="text-muted-foreground">
                                    Direccion
                                </span>
                                <span className="text-right">
                                    {order.shipping_address.recipient_name},{' '}
                                    {order.shipping_address.line1}
                                    {order.shipping_address.line2
                                        ? `, ${order.shipping_address.line2}`
                                        : ''}
                                    , {order.shipping_address.city},{' '}
                                    {order.shipping_address.state}{' '}
                                    {order.shipping_address.postal_code}
                                </span>
                            </div>
                        )}
                        {order.payment_method && (
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    Pago
                                </span>
                                <span className="uppercase">
                                    {order.payment_method}
                                </span>
                            </div>
                        )}
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Creado
                            </span>
                            <span>{formatDate(order.created_at)}</span>
                        </div>
                    </CardContent>
                </Card>

                {order.production_stage && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                Produccion
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <Pipeline current={order.production_stage} />

                            <div className="flex flex-wrap items-center gap-6">
                                {canAdvanceProduction && (
                                    <div className="flex items-center gap-2">
                                        <span className="text-sm text-muted-foreground">
                                            Etapa:
                                        </span>
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
                                                {productionStages.map(
                                                    (stage) => (
                                                        <SelectItem
                                                            key={stage}
                                                            value={stage}
                                                        >
                                                            {stageLabels[stage]}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                )}

                                {canCheckQuality && (
                                    <label className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            checked={order.quality_checked}
                                            onCheckedChange={(checked) =>
                                                handleQualityCheckChange(
                                                    order.id,
                                                    checked === true,
                                                )
                                            }
                                        />
                                        Control de calidad aprobado
                                    </label>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Bitacora</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <form onSubmit={submitNote} className="space-y-2">
                            <Textarea
                                value={noteForm.data.body}
                                onChange={(event) =>
                                    noteForm.setData('body', event.target.value)
                                }
                                placeholder="Agrega una nota, por ejemplo: cliente pidio cambiar el color, ya se le avisara."
                                rows={2}
                            />
                            <InputError message={noteForm.errors.body} />
                            <div className="flex justify-end">
                                <Button
                                    type="submit"
                                    size="sm"
                                    disabled={
                                        noteForm.processing ||
                                        noteForm.data.body.trim() === ''
                                    }
                                >
                                    Agregar nota
                                </Button>
                            </div>
                        </form>

                        {events.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Todavia no hay actividad en este pedido.
                            </p>
                        ) : (
                            <div className="space-y-4 border-t pt-4">
                                {events.map((event, eventIndex) => (
                                    <TimelineEntry
                                        key={eventIndex}
                                        event={event}
                                    />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

OrderShow.layout = {
    breadcrumbs: [
        { title: 'Pedidos', href: index() },
        { title: 'Detalle', href: '' },
    ],
};
