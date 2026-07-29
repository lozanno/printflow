<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $component_id
 * @property string $value
 * @property string $label
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['component_id', 'value', 'label', 'sort_order'])]
class ComponentOption extends Model
{
    /**
     * @return BelongsTo<Component, $this>
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * @return HasMany<OptionPriceModifier, $this>
     */
    public function priceModifiers(): HasMany
    {
        return $this->hasMany(OptionPriceModifier::class);
    }
}
