<?php

declare(strict_types=1);

namespace App\ChargingSlots\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\SlotAvailabilityRules;
use Illuminate\Support\Collection;

final readonly class SlotAvailability implements SlotAvailabilityRules
{
    /**
     * @param  Collection<int, ChargingRequest>  $assignedRequests
     */
    public function __construct(private Collection $assignedRequests) {}

    public function isAvailable(ChargingSlot $slot, ChargingWindow $window): bool
    {
        return ! $this->hasConflictWith($slot, $window);
    }

    private function hasConflictWith(ChargingSlot $slot, ChargingWindow $window): bool
    {
        return $this->requestsAssignedTo($slot)
            ->contains(fn (ChargingRequest $request) => $request->conflictsWith($window));
    }

    /**
     * @return Collection<int, ChargingRequest>
     */
    private function requestsAssignedTo(ChargingSlot $slot): Collection
    {
        return $this->assignedRequests
            ->filter(fn (ChargingRequest $request) => $request->slot_id === $slot->id);
    }
}
