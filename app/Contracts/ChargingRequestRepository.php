<?php

declare(strict_types=1);

namespace App\Contracts;

use App\ChargingRequests\ChargingRequest;
use App\Users\User;

interface ChargingRequestRepository
{
    public function save(ChargingRequest $chargingRequest): void;

    public function hasActiveRequestFor(User $user): void;
}
