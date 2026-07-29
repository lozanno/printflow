<?php

namespace App\Enums;

enum DeliveryType: string
{
    case Ship = 'SHIP';
    case Pickup = 'PICKUP';
}
