<?php

namespace App\QuoteEngine;

final readonly class QuoteResult
{
    /**
     * @param  list<AppliedModifier>  $modifiers
     */
    public function __construct(
        public float $basePrice,
        public array $modifiers,
        public float $total,
        public string $currency,
    ) {}
}
