<?php

declare(strict_types=1);

namespace App\Contracts;

use App\ChargingSlots\ChargingSlot;
use Illuminate\Support\Collection;

interface ChargingSlotRepository
{
    public function save(ChargingSlot $slot): void;

    /**
     * @return Collection<int, ChargingSlot>
     */
    public function list(): Collection;
}
