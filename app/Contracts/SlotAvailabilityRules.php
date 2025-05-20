<?php

declare(strict_types=1);

namespace App\Contracts;

use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;

interface SlotAvailabilityRules
{
    public function isAvailable(ChargingSlot $slot, ChargingWindow $window): bool;
}
