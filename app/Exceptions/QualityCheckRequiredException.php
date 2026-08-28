<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by Order::advanceProductionStage() when someone tries to move an
 * order to READY/DELIVERED before Calidad has signed off - the same way
 * QuoteCannotBeCalculatedException stops a bad price from ever being
 * quoted, this stops the transition from ever being persisted, regardless
 * of what the UI does or doesn't disable.
 */
class QualityCheckRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Este pedido necesita pasar control de calidad antes de continuar.');
    }
}
