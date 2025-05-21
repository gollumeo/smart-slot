<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;

final readonly class MarkChargingRequestAsStarted
{
    /**
     * @throws CannotStartChargingRequest
     * @throws CannotAssignRequestWithoutSlot
     */
    public function execute(ChargingRequest $chargingRequest): void
    {
        $chargingRequest->markAs(ChargingRequestStatus::CHARGING);
    }
}
