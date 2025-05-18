<?php

declare(strict_types=1);

namespace App\ChargingRequests\Infrastructure\Repositories;

use App\ChargingRequests\ChargingRequest;
use App\Contracts\ChargingRequestRepository;
use App\Users\User;

final class ChargingRequestsEloquent implements ChargingRequestRepository
{
    public function save(ChargingRequest $chargingRequest): void
    {
        // TODO: Implement save() method.
    }

    public function hasActiveRequestFor(User $user): void
    {
        // TODO: Implement hasActiveRequestFor() method.
    }
}
