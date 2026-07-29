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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['pricing_profile_id', 'min_quantity', 'max_quantity', 'unit_price'])]
class PricingTier extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PricingProfile, $this>
     */
    public function pricingProfile(): BelongsTo
    {
        return $this->belongsTo(PricingProfile::class);
    }
}
