import type { DeliveryType, OrderStatus, ProductionStage } from '@/types/admin';

type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline';

export const statusLabels: Record<OrderStatus, string> = {
    PENDING_PAYMENT: 'Pendiente de pago',
    PAID: 'Pagado',
    COMPLETED: 'Completado',
    CANCELLED: 'Cancelado',
};

export const statusVariants: Record<OrderStatus, BadgeVariant> = {
    PENDING_PAYMENT: 'outline',
    PAID: 'default',
    COMPLETED: 'secondary',
    CANCELLED: 'destructive',
};

export const deliveryLabels: Record<DeliveryType, string> = {
    PICKUP: 'Recoger en tienda',
    SHIP: 'Envio a domicilio',
};

/** Order matters here - it's also the pipeline's left-to-right sequence. */
export const productionStages: ProductionStage[] = [
    'PENDING',
    'IN_PRODUCTION',
    'QUALITY_CHECK',
    'READY',
    'DELIVERED',
];

export const stageLabels: Record<ProductionStage, string> = {
    PENDING: 'Pendiente',
    IN_PRODUCTION: 'En produccion',
    QUALITY_CHECK: 'Control de calidad',
    READY: 'Listo para entrega',
    DELIVERED: 'Entregado',
};

export const stageVariants: Record<ProductionStage, BadgeVariant> = {
    PENDING: 'outline',
    IN_PRODUCTION: 'secondary',
    QUALITY_CHECK: 'secondary',
    READY: 'default',
    DELIVERED: 'secondary',
};

export function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency,
    }).format(amount);
}

export function formatDate(iso: string | null): string {
    if (!iso) {
        return '-';
    }

    return new Intl.DateTimeFormat('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));
}
