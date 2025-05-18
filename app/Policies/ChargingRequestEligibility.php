<?php

namespace App\Policies;

use App\Contracts\ChargingRequestRepository;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use App\Users\User;

readonly class ChargingRequestEligibility
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private ChargingRequestRepository $repository)
    {}

    public function ensureUserCanStart(User $user): void
    {
        if ($this->repository->hasActiveRequestFor($user)) {
            throw new UserAlreadyHasActiveChargingRequest::class;
        }
    }
}
