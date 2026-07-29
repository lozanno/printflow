<?php

namespace App\QuoteEngine\Exceptions;

use App\Models\CatalogProduct;
use RuntimeException;

final class QuoteCannotBeCalculatedException extends RuntimeException
{
    public static function missingPricingProfile(CatalogProduct $catalogProduct): self
    {
        return new self("Catalog product [{$catalogProduct->id}] has no pricing profile.");
    }

    public static function missingSelection(string $componentCode): self
    {
        return new self("Missing a value for required component [{$componentCode}].");
    }

    public static function invalidSelection(string $componentCode, mixed $value): self
    {
        $encoded = json_encode($value) ?: 'null';

        return new self("Invalid value for component [{$componentCode}]: {$encoded}.");
    }

    public static function noTierForQuantity(int $quantity): self
    {
        return new self("No pricing tier covers a quantity of [{$quantity}].");
    }

    public static function missingPricingParam(string $key): self
    {
        return new self("Missing pricing parameter [{$key}] on the pricing profile.");
    }
}
