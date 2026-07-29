<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
])]
class Shop extends Model
{
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
}
