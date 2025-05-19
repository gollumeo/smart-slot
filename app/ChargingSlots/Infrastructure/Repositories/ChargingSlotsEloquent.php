<?php

declare(strict_types=1);

namespace App\ChargingSlots\Infrastructure\Repositories;

use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingSlotRepository;

final class ChargingSlotsEloquent implements ChargingSlotRepository
{
    public function findAvailableSlot(): ?ChargingSlot
    {
        // TODO: Implement findAvailableSlot() method.

        return new ChargingSlot();
    }

    public function save(ChargingSlot $slot): void
    {
        // TODO: Implement save() method.
    }
}
