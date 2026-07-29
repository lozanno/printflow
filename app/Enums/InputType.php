<?php

namespace App\Enums;

enum InputType: string
{
    case Choice = 'CHOICE';
    case Number = 'NUMBER';
    case Dimensions = 'DIMENSIONS';
}
