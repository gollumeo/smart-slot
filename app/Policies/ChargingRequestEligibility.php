<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\ChargingRequestRepository;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use App\Users\User;

class ChargingRequestEligibility
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private readonly ChargingRequestRepository $repository) {}

    /**
     * @throws UserAlreadyHasActiveChargingRequest
     */
    public function ensureUserCanStart(User $user): void
    {
        if ($this->repository->hasActiveRequestFor($user)) {
            throw new UserAlreadyHasActiveChargingRequest();
        }
    }
}
