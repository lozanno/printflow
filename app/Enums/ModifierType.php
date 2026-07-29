<?php

namespace App\Enums;

enum ModifierType: string
{
    case FixedAdd = 'FIXED_ADD';
    case PercentMultiply = 'PERCENT_MULTIPLY';
    case PerUnitAdd = 'PER_UNIT_ADD';
}
