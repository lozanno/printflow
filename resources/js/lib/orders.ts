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

/**
 * Colors are meaningful, not decorative: green only ever means Entregado,
 * yellow only ever means Pendiente, so nobody has to read the label to
 * know a shop-floor board is on track. `needsSalesAttention` overrides
 * whatever the stage would otherwise show with white - ventas hasn't
 * cleared this order to move forward, regardless of where it sits.
 */
export const stageColorClasses: Record<ProductionStage, string> = {
    PENDING:
        'border-yellow-300 bg-yellow-100 text-yellow-900 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200',
    IN_PRODUCTION:
        'border-blue-300 bg-blue-100 text-blue-900 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200',
    QUALITY_CHECK:
        'border-purple-300 bg-purple-100 text-purple-900 dark:border-purple-800 dark:bg-purple-950 dark:text-purple-200',
    READY: 'border-orange-300 bg-orange-100 text-orange-900 dark:border-orange-800 dark:bg-orange-950 dark:text-orange-200',
    DELIVERED:
        'border-green-300 bg-green-100 text-green-900 dark:border-green-800 dark:bg-green-950 dark:text-green-200',
};

export const salesAttentionColorClasses =
    'border-neutral-300 bg-white text-neutral-900 dark:border-neutral-600 dark:bg-neutral-100 dark:text-neutral-900';

export function stageBadgeClasses(
    stage: ProductionStage,
    needsSalesAttention: boolean,
): string {
    return needsSalesAttention
        ? salesAttentionColorClasses
        : stageColorClasses[stage];
}

/** A lighter tint of the same stage color, for tinting a whole table row
 * instead of just a badge. Blanco (needs sales attention) means "no
 * color" here - the row just keeps its normal background. */
export const stageRowColorClasses: Record<ProductionStage, string> = {
    PENDING: 'bg-yellow-50 dark:bg-yellow-950/40',
    IN_PRODUCTION: 'bg-blue-50 dark:bg-blue-950/40',
    QUALITY_CHECK: 'bg-purple-50 dark:bg-purple-950/40',
    READY: 'bg-orange-50 dark:bg-orange-950/40',
    DELIVERED: 'bg-green-50 dark:bg-green-950/40',
};

export function stageRowClasses(
    stage: ProductionStage,
    needsSalesAttention: boolean,
): string {
    return needsSalesAttention ? '' : stageRowColorClasses[stage];
}

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

/** For date-only values (e.g. estimated_delivery_date) - forces UTC so a
 * plain "YYYY-MM-DD" doesn't shift a day in negative-offset timezones. */
export function formatDateOnly(date: string | null): string {
    if (!date) {
        return '-';
    }

    return new Intl.DateTimeFormat('es-MX', {
        dateStyle: 'medium',
        timeZone: 'UTC',
    }).format(new Date(date));
}
