<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class ChargingRequestAlreadyFinished extends Exception
{
    public function __construct()
    {
        parent::__construct('Charging request already finished.');
    }
}
