<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotStartChargingRequest extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot start an unassigned charging request.');
    }
}
