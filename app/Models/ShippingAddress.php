<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property string $recipient_name
 * @property string $phone
 * @property string $line1
 * @property string|null $line2
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'order_id',
    'recipient_name',
    'phone',
    'line1',
    'line2',
    'city',
    'state',
    'postal_code',
    'country',
])]
class ShippingAddress extends Model
{
    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
