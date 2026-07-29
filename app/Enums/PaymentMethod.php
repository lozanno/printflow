<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case Oxxo = 'oxxo';
    case Spei = 'spei';
}
