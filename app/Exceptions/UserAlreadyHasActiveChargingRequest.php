<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class UserAlreadyHasActiveChargingRequest extends Exception
{
    public function __construct()
    {
        parent::__construct('User already has active charging request.');
    }
}
