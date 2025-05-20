<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingSlots\ChargingSlot;
use App\ChargingSlots\Write\SlotAvailability;
use Tests\TestCase;

describe('Unit: Slot Availability', function (): void {
    it('is not available if a charging request has already been assigned to this slot', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = $this->createWindow('20-05-2025 09:00', '20-05-2025 17:00');
        $batteryPercentage = new BatteryPercentage(50);

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: $batteryPercentage,
            chargingWindow: $chargingWindow,
            status: ChargingRequestStatus::ASSIGNED
        );
        $chargingRequest->slot_id = 42;

        $conflictingWindow = $this->createWindow('20-05-2025 13:00', '20-05-2025 15:00');

        $chargingSlot = new ChargingSlot();
        $chargingSlot->id = 42;

        $slotAvailability = new SlotAvailability(collect([$chargingRequest]));

        $isSlotAvailable = $slotAvailability->isAvailable($chargingSlot, $conflictingWindow);

        expect($isSlotAvailable)->toBeFalse();
    });

    it('is available if no charging request is assigned for the given window', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = $this->createWindow('20-05-2025 09:00', '20-05-2025 12:00');
        $batteryPercentage = new BatteryPercentage(50);

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: $batteryPercentage,
            chargingWindow: $chargingWindow,
            status: ChargingRequestStatus::ASSIGNED
        );
        $chargingRequest->slot_id = 42;

        $notConflictingWindow = $this->createWindow('20-05-2025 13:00', '20-05-2025 15:00');

        $chargingSlot = new ChargingSlot();
        $chargingSlot->id = 42;

        $slotAvailability = new SlotAvailability(collect([$chargingRequest]));

        $isSlotAvailable = $slotAvailability->isAvailable($chargingSlot, $notConflictingWindow);

        expect($isSlotAvailable)->toBeTrue();
    });
});
