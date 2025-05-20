<?php

declare(strict_types=1);

namespace App\ChargingSlots\Read;

use App\ChargingRequests\ChargingRequest;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingRequestRepository;
use App\Contracts\ChargingSlotRepository;
use Illuminate\Support\Collection;

final readonly class ChargingSlotsView
{
    public function __construct(
        private ChargingSlotRepository $slots,
        private ChargingRequestRepository $requests
    ) {}

    /**
     * @return Collection<int, ChargingSlot>
     */
    public function get(): Collection
    {
        return $this->slots->list();
    }

    /**
     * @return Collection<int, ChargingSlot>
     */
    public function available(): Collection
    {
        return $this->slots->list()
            ->filter(fn (ChargingSlot $slot) => $this->isSlotAvailable($slot));
    }

    private function isSlotAvailable(ChargingSlot $slot): bool
    {
        return ! $this->ongoingRequests()
            ->contains(fn (ChargingRequest $request) => $request->slot_id === $slot->id);
    }

    /**
     * @return Collection<int, ChargingRequest>
     */
    private function ongoingRequests(): Collection
    {
        $requests = $this->requests->getPendingRequests();

        /** @var Collection<int, ChargingRequest> $requests */
        return $requests->filter(function ($request) {
            /** @var ChargingRequest $request */
            return ! $request->status->isTerminal();
        });
    }
}
