<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'ADMIN';
    case Ventas = 'VENTAS';
    case Administrativo = 'ADMINISTRATIVO';
    case Produccion = 'PRODUCCION';
    case Calidad = 'CALIDAD';
}
