<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\Contracts\ChargingRequestRepository;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use App\Policies\ChargingRequestEligibility;
use App\Users\User;

final readonly class StartChargingRequest
{
    public function __construct(
        private ChargingRequestRepository $repository,
        private AssignSlotToRequest $assignSlot,
        private ChargingRequestEligibility $eligibility,
    ) {}

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     * @throws CannotStartChargingRequest
     * @throws UserAlreadyHasActiveChargingRequest
     */
    public function execute(User $user, ChargingWindow $chargingWindow, BatteryPercentage $batteryPercentage): ChargingRequest
    {
        $this->eligibility->ensureUserCanStart($user);

        $chargingRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow);
        $this->repository->save($chargingRequest);

        ($this->assignSlot)($chargingRequest);

        return $chargingRequest;
    }
}
