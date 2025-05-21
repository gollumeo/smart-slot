<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\Contracts\ChargingRequestRepository;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;

final class CancelChargingRequest
{
    public function __construct(
        private readonly ChargingRequestRepository $requests,
        private readonly AssignSlotToRequest $assignSlot,
        private SelectNextRequestToAssign $selectNextRequest,
    ) {}

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws CannotStartChargingRequest
     * @throws ChargingRequestAlreadyFinished
     */
    public function execute(ChargingRequest $chargingRequest): void
    {
        $chargingRequest->markAs(ChargingRequestStatus::CANCELLED);

        $this->requests->save($chargingRequest);
    }
}
