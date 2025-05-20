<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;

final readonly class MarkChargingRequestAsStarted
{
    public function __construct(private ChargingRequest $chargingRequest) {}

    public function __invoke(): void
    {
        $this->chargingRequest->markAs(ChargingRequestStatus::CHARGING);
    }
}
