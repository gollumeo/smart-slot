<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\Contracts\ChargingRequestRepository;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;

final readonly class EndChargingRequest
{
    public function __construct(
        private ChargingRequestRepository $chargingRequests,
        private AssignSlotToRequest $assignSlot,
        private SelectNextRequestToAssign $selectNextRequest,
    ) {}

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     * @throws CannotStartChargingRequest
     */
    public function execute(ChargingRequest $chargingRequest): void
    {
        if ($chargingRequest->status->isTerminal()) {
            throw new ChargingRequestAlreadyFinished();
        }

        $chargingRequest->markAs(ChargingRequestStatus::DONE);
        $this->chargingRequests->save($chargingRequest);

        $queuedRequests = $this->chargingRequests->getPendingRequests();
        $next = ($this->selectNextRequest)($queuedRequests);

        if ($next) {
            ($this->assignSlot)($next);
        }
    }
}
