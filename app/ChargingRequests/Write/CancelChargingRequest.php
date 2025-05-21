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
        private readonly ChargingRequestRepository $chargingRequests,
        private readonly AssignSlotToRequest $assignSlot,
        private readonly SelectNextRequestToAssign $selectNextRequest,
    ) {}

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws CannotStartChargingRequest
     * @throws ChargingRequestAlreadyFinished
     */
    public function execute(ChargingRequest $chargingRequest): void
    {
        if ($chargingRequest->status->isTerminal()) {
            throw new ChargingRequestAlreadyFinished();
        }

        $wasAssigned = $chargingRequest->status->isAssigned();

        $chargingRequest->markAs(ChargingRequestStatus::CANCELLED);
        $this->chargingRequests->save($chargingRequest);

        if (! $wasAssigned) {
            return;
        }

        $queuedRequests = $this->chargingRequests->getPendingRequests();
        $next = ($this->selectNextRequest)($queuedRequests);

        if ($next) {
            ($this->assignSlot)($next);
        }
    }
}
