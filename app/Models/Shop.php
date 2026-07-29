<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $currency
 * @property string|null $pickup_line1
 * @property string|null $pickup_line2
 * @property string|null $pickup_city
 * @property string|null $pickup_state
 * @property string|null $pickup_postal_code
 * @property string|null $pickup_phone
 * @property string|null $logo_path
 * @property string|null $logo_url
 * @property string|null $brand_color
 * @property string|null $contact_email
 * @property string|null $facebook_url
 * @property string|null $instagram_url
 * @property string|null $whatsapp_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'slug',
    'currency',
    'pickup_line1',
    'pickup_line2',
    'pickup_city',
    'pickup_state',
    'pickup_postal_code',
    'pickup_phone',
    'logo_path',
    'brand_color',
    'contact_email',
    'facebook_url',
    'instagram_url',
    'whatsapp_url',
])]
class Shop extends Model
{
    /**
     * @var list<string>
     */
    protected $appends = ['logo_url'];

    /**
     * Resolves the shop the admin panel is managing. Single-tenant for now
     * (exactly one row is expected) - this is the one seam to change when
     * the app grows into multi-tenant and needs to resolve the shop from
     * the authenticated user instead.
     */
    public static function current(): self
    {
        return self::sole();
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
        );
    }

    /**
     * @return HasMany<CatalogProduct, $this>
     */
    public function catalogProducts(): HasMany
    {
        return $this->hasMany(CatalogProduct::class);
    }

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * @return HasMany<Page, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
