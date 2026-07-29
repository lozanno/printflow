<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pricing_profile_id
 * @property int $min_quantity
 * @property int|null $max_quantity
 * @property string $unit_price
 * @property string|null $adjustment_percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['pricing_profile_id', 'min_quantity', 'max_quantity', 'unit_price', 'adjustment_percent'])]
class PricingTier extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'adjustment_percent' => 'decimal:3',
        ];
    }

    /**
     * @return BelongsTo<PricingProfile, $this>
     */
    public function pricingProfile(): BelongsTo
    {
        return $this->belongsTo(PricingProfile::class);
    }

    /**
     * The unit price after applying this tier's own percentage adjustment
     * (e.g. a promotional discount baked into a specific quantity break).
     * This is intentionally never exposed to the storefront as a raw
     * percentage - only its effect on the price is.
     */
    public function effectiveUnitPrice(): float
    {
        $adjustment = $this->adjustment_percent !== null ? (float) $this->adjustment_percent : 0.0;

        return round((float) $this->unit_price * (1 + $adjustment / 100), 4);
    }
}
