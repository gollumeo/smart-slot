<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingSlotRepository;
use App\Contracts\SlotAvailabilityRules;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;

class AssignSlotToRequest
{
    public function __construct(
        private readonly ChargingSlotRepository $slots,
        private readonly SlotAvailabilityRules $slotAvailability,
    ) {}

    /**
     * @throws CannotStartChargingRequest
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     */
    public function __invoke(ChargingRequest $chargingRequest): void
    {
        $availableSlot = $this->findFirstAvailableSlotFor($chargingRequest->chargingWindow());

        if ($availableSlot) {
            $chargingRequest->assignTo($availableSlot);
        }
    }

    private function findFirstAvailableSlotFor(ChargingWindow $window): ?ChargingSlot
    {
        foreach ($this->slots->list() as $slot) {
            if ($this->slotAvailability->isAvailable($slot, $window)) {
                return $slot;
            }
        }
        return null;
    }
}
