<?php

namespace App\Models;

use App\Enums\InputType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $label
 * @property InputType $input_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductTemplateComponent|null $pivot Set only when loaded via ProductTemplate::components().
 */
#[Fillable(['code', 'label', 'input_type'])]
class Component extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_type' => InputType::class,
        ];
    }

    /**
     * @return HasMany<ComponentOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ComponentOption::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<ProductTemplate, $this, ProductTemplateComponent>
     */
    public function productTemplates(): BelongsToMany
    {
        return $this->belongsToMany(ProductTemplate::class, 'product_template_components')
            ->using(ProductTemplateComponent::class)
            ->withPivot(['sort_order', 'is_required']);
    }
}
