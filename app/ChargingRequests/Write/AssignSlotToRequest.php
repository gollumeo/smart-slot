<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\Contracts\ChargingSlotRepository;

class AssignSlotToRequest
{
    public function __construct(private readonly ChargingSlotRepository $slots)
    {}

    public function __invoke(ChargingRequest $chargingRequest): void
    {
        $slot = $this->slots->findAvailableSlot();

        if ($slot) {
            $chargingRequest->slot_id = $slot->id;
            $chargingRequest->markAs(ChargingRequestStatus::ASSIGNED);
        }
    }
}
