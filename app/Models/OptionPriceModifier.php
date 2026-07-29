<?php

namespace App\Models;

use App\Enums\ModifierType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pricing_profile_id
 * @property int $component_option_id
 * @property ModifierType $modifier_type
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['pricing_profile_id', 'component_option_id', 'modifier_type', 'value'])]
class OptionPriceModifier extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modifier_type' => ModifierType::class,
            'value' => 'decimal:4',
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
     * @return BelongsTo<ComponentOption, $this>
     */
    public function componentOption(): BelongsTo
    {
        return $this->belongsTo(ComponentOption::class);
    }
}
