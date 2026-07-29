<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $catalog_product_id
 * @property array<string, mixed>|null $params
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['catalog_product_id', 'params'])]
class PricingProfile extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'params' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CatalogProduct, $this>
     */
    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class);
    }

    /**
     * @return HasMany<PricingTier, $this>
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(PricingTier::class)->orderBy('min_quantity');
    }

    /**
     * @return HasMany<OptionPriceModifier, $this>
     */
    public function optionModifiers(): HasMany
    {
        return $this->hasMany(OptionPriceModifier::class);
    }
}
