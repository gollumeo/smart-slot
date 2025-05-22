<?php

declare(strict_types=1);

namespace App\ChargingSlots\Infrastructure\Repositories;

use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingSlotRepository;
use Illuminate\Support\Collection;

final class ChargingSlotsEloquent implements ChargingSlotRepository
{
    public function save(ChargingSlot $slot): void
    {
        $slot->save();
    }

    public function list(): Collection
    {
        return ChargingSlot::all();
    }
}
