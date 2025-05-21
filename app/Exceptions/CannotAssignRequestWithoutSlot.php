<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotAssignRequestWithoutSlot extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot assign a request without a slot.');
    }
}
