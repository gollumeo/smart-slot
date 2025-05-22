<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingSlots\Write\SlotAvailability;
use App\Contracts\ChargingRequestRepository;
use App\Contracts\ChargingSlotRepository;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use Illuminate\Support\Facades\DB;

final readonly class EndChargingRequest
{
    public function __construct(
        private ChargingRequestRepository $chargingRequests,
        private ChargingSlotRepository $slots,
        private SelectNextRequestToAssign $selectNextRequest,
    ) {}

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     * @throws CannotStartChargingRequest
     */
    public function execute(ChargingRequest $chargingRequest): void
    {
        if ($chargingRequest->status->isTerminal()) {
            throw new ChargingRequestAlreadyFinished();
        }

        $chargingRequest->markAs(ChargingRequestStatus::DONE);
        $this->chargingRequests->save($chargingRequest);
        DB::table('charging_requests')
            ->where('id', $chargingRequest->id)
            ->update(['status' => ChargingRequestStatus::DONE->value]);

        $assignedRequests = ChargingRequest::query()
            ->whereIn('status', [
                ChargingRequestStatus::ASSIGNED,
                ChargingRequestStatus::CHARGING,
            ])
            ->get();

        $slotAvailability = new SlotAvailability($assignedRequests);
        $assignSlot = new AssignSlotToRequest($this->slots, $slotAvailability);

        $queuedRequests = $this->chargingRequests->getPendingRequests();
        $next = ($this->selectNextRequest)($queuedRequests);

        if ($next) {
            $assignSlot($next);

            DB::table('charging_requests')
                ->where('id', $next->id)
                ->update([
                    'status' => ChargingRequestStatus::ASSIGNED->value,
                    'slot_id' => $next->slot_id,
                    'updated_at' => now(),
                ]);
        }
    }
}
