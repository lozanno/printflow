<?php

namespace App\Models;

use App\Enums\PricingStrategy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property PricingStrategy $pricing_strategy
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'pricing_strategy'])]
class ProductTemplate extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pricing_strategy' => PricingStrategy::class,
        ];
    }

    /**
     * @return HasMany<ProductTemplateComponent, $this>
     */
    public function templateComponents(): HasMany
    {
        return $this->hasMany(ProductTemplateComponent::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<Component, $this, ProductTemplateComponent>
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'product_template_components')
            ->using(ProductTemplateComponent::class)
            ->withPivot(['sort_order', 'is_required'])
            ->orderByPivot('sort_order');
    }

    /**
     * @return HasMany<CatalogProduct, $this>
     */
    public function catalogProducts(): HasMany
    {
        return $this->hasMany(CatalogProduct::class);
    }
}
