<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'PENDING_PAYMENT';
    case Paid = 'PAID';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
}
