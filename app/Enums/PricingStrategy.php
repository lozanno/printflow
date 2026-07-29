<?php

namespace App\Enums;

enum PricingStrategy: string
{
    case PerUnitTiered = 'PER_UNIT_TIERED';
    case PerArea = 'PER_AREA';
    case PerAreaWithSetup = 'PER_AREA_WITH_SETUP';
}
