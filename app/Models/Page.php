<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shop_id
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['shop_id', 'title', 'slug', 'content', 'is_published'])]
class Page extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
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
     * Content is rendered as raw HTML on the storefront, so it's sanitized
     * on the way in - see HtmlSanitizer for why.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => HtmlSanitizer::sanitize($value),
        );
    }
}
