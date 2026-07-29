<?php

namespace App\QuoteEngine;

use App\Enums\ModifierType;

final readonly class AppliedModifier
{
    public function __construct(
        public string $label,
        public ModifierType $type,
        public float $amount,
    ) {}
}
