<?php

declare(strict_types=1);

namespace App\ChargingRequests\Read;

use App\ChargingRequests\ChargingRequest;
use App\Contracts\ChargingRequestRepository;
use App\Users\User;

final readonly class ChargingRequestView
{
    public function __construct(private ChargingRequestRepository $repository) {}

    public function for(User $user): ?ChargingRequest
    {
        return $this->repository->getActiveRequestFor($user);
    }
}
