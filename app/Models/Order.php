<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\ProductionStage;
use App\Exceptions\QualityCheckRequiredException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shop_id
 * @property int $customer_id
 * @property DeliveryType $delivery_type
 * @property Carbon|null $estimated_delivery_date
 * @property bool $is_urgent
 * @property OrderStatus $status
 * @property ProductionStage|null $production_stage
 * @property bool $needs_sales_attention
 * @property Carbon|null $quality_checked_at
 * @property int|null $quality_checked_by_user_id
 * @property string $subtotal
 * @property string $shipping_cost
 * @property string $total
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'shop_id',
    'customer_id',
    'delivery_type',
    'estimated_delivery_date',
    'is_urgent',
    'status',
    'production_stage',
    'needs_sales_attention',
    'quality_checked_at',
    'quality_checked_by_user_id',
    'subtotal',
    'shipping_cost',
    'total',
    'currency',
])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_type' => DeliveryType::class,
            'estimated_delivery_date' => 'date',
            'is_urgent' => 'boolean',
            'status' => OrderStatus::class,
            'production_stage' => ProductionStage::class,
            'needs_sales_attention' => 'boolean',
            'quality_checked_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasOne<ShippingAddress, $this>
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<OrderStageChange, $this>
     */
    public function stageChanges(): HasMany
    {
        return $this->hasMany(OrderStageChange::class);
    }

    /**
     * @return HasMany<OrderNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function qualityCheckedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_checked_by_user_id');
    }

    /**
     * Moves the order to a new production stage and records who did it
     * and when - "controla cada etapa" means an audit trail, not just
     * the current value.
     *
     * @throws QualityCheckRequiredException if moving to READY/DELIVERED
     *                                       before Calidad has signed off
     */
    public function advanceProductionStage(ProductionStage $stage, ?int $changedByUserId): void
    {
        $requiresQualityCheck = in_array($stage, [ProductionStage::Ready, ProductionStage::Delivered], true);

        if ($requiresQualityCheck && $this->quality_checked_at === null) {
            throw new QualityCheckRequiredException;
        }

        $this->stageChanges()->create([
            'from_stage' => $this->production_stage,
            'to_stage' => $stage,
            'changed_by_user_id' => $changedByUserId,
        ]);

        $this->update(['production_stage' => $stage]);
    }

    /**
     * Calidad's sign-off gate. Passing false un-checks it (correcting a
     * mistake), the same way production stages can move backward freely.
     */
    public function setQualityChecked(bool $passed, int $userId): void
    {
        $this->update([
            'quality_checked_at' => $passed ? now() : null,
            'quality_checked_by_user_id' => $passed ? $userId : null,
        ]);
    }
}
