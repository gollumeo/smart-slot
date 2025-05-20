<?php

declare(strict_types=1);

namespace App\ChargingSlots\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;
use Illuminate\Support\Collection;

final readonly class SlotAvailability
{
    /**
     * @param  Collection<int, ChargingRequest>  $assignedRequests
     */
    public function __construct(private Collection $assignedRequests) {}

    public function isAvailable(ChargingSlot $slot, ChargingWindow $window): bool
    {
        return ! $this->assignedRequests
            ->filter(fn (ChargingRequest $request) => $request->slot_id === $slot->id)
            ->contains(fn (ChargingRequest $request) => $request->starts_at < $window->end() && $request->ends_at > $window->start()
            );
    }
}
