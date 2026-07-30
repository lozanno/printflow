<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $shop_id
 * @property int $product_template_id
 * @property string|null $name_override
 * @property string|null $slug
 * @property string|null $image_path
 * @property string|null $image_url
 * @property string|null $description
 * @property string|null $details_content
 * @property bool $is_active
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'shop_id',
    'product_template_id',
    'name_override',
    'slug',
    'image_path',
    'description',
    'details_content',
    'is_active',
    'is_featured',
])]
class CatalogProduct extends Model
{
    /**
     * @var list<string>
     */
    protected $appends = ['image_url'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
        );
    }

    /**
     * Rendered as raw HTML on the storefront, so it's sanitized on the way
     * in - see HtmlSanitizer for why.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function detailsContent(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => HtmlSanitizer::sanitize($value),
        );
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo<ProductTemplate, $this>
     */
    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    /**
     * @return HasOne<PricingProfile, $this>
     */
    public function pricingProfile(): HasOne
    {
        return $this->hasOne(PricingProfile::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'catalog_product_category');
    }

    /**
     * @return HasMany<CatalogProductFaq, $this>
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(CatalogProductFaq::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<CatalogProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CatalogProductReview::class)->orderBy('sort_order');
    }
}
