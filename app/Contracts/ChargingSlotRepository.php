<?php

declare(strict_types=1);

namespace App\Contracts;

use App\ChargingSlots\ChargingSlot;

interface ChargingSlotRepository
{
    public function findAvailableSlot(): ?ChargingSlot;

    public function save(ChargingSlot $slot): void;
}
