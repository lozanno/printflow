<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $catalog_product_id
 * @property string $question
 * @property string $answer
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['catalog_product_id', 'question', 'answer', 'sort_order'])]
class CatalogProductFaq extends Model
{
    /**
     * @return BelongsTo<CatalogProduct, $this>
     */
    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class);
    }
}
