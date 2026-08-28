<?php

namespace App\Enums;

/**
 * Tracks where an order physically is on the shop floor - a separate axis
 * from OrderStatus (which only tracks payment: pending/paid/completed/
 * cancelled). An order can be PAID and still be at any of these stages.
 */
enum ProductionStage: string
{
    case Pending = 'PENDING';
    case InProduction = 'IN_PRODUCTION';
    case QualityCheck = 'QUALITY_CHECK';
    case Ready = 'READY';
    case Delivered = 'DELIVERED';
}
