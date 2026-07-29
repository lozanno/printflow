<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_template_id
 * @property int $component_id
 * @property int $sort_order
 * @property bool $is_required
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['product_template_id', 'component_id', 'sort_order', 'is_required'])]
class ProductTemplateComponent extends Pivot
{
    // Pivot::getTable() defaults to the singular of the class name
    // (AsPivot::getTable()), which doesn't match the migration's plural
    // table name.
    protected $table = 'product_template_components';

    public $incrementing = true;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ProductTemplate, $this>
     */
    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    /**
     * @return BelongsTo<Component, $this>
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }
}
