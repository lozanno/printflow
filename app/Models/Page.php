<?php

namespace App\Models;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

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
     * on the way in (not just trusted because only admins can write it) -
     * this stays safe even if an admin account is ever compromised, or the
     * app grows into multi-tenant with less-trusted per-shop staff.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value !== null ? self::purifier()->purify($value) : null,
        );
    }

    private static function purifier(): HTMLPurifier
    {
        static $purifier = null;

        if ($purifier === null) {
            $cachePath = storage_path('framework/cache/htmlpurifier');
            File::ensureDirectoryExists($cachePath);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,br,strong,em,h2,h3,ul,ol,li,a[href],img[src|alt],blockquote,code,pre,hr');
            $config->set('AutoFormat.RemoveEmpty', true);
            $config->set('Cache.SerializerPath', $cachePath);

            $purifier = new HTMLPurifier($config);
        }

        return $purifier;
    }
}
