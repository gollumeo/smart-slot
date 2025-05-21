<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;

final readonly class MarkChargingRequestAsStarted
{
    public function execute(ChargingRequest $chargingRequest): void
    {
        $chargingRequest->markAs(ChargingRequestStatus::CHARGING);
    }
}
