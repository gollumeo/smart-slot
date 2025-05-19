<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingSlotRepository;
use Carbon\CarbonImmutable;

describe('Unit: Assign Slot To Charging Request', function (): void {
    it('confirms the user\'s request by assigning it to an available charging slot', function () {
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(25);
        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 09:00');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 12:00');
        $chargingWindow = new ChargingWindow($start, $end);

        $chargingRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow, ChargingRequestStatus::QUEUED);

        $slot = new ChargingSlot();
        $slot->id = 42;

        $slotRepo = Mockery::mock(ChargingSlotRepository::class);
        $slotRepo->shouldReceive('findAvailableSlot')->once()->andReturn($slot);

        $assignSlot = new AssignSlotToRequest($slotRepo);
        $assignSlot($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::ASSIGNED)
            ->and($chargingRequest->slot_id)->toBe(42);
    });

    it('places the request in waiting line when all charging spots are occupied', function () {
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(50);
        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 13:00');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 17:00');
        $chargingWindow = new ChargingWindow($start, $end);

        $chargingRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow, ChargingRequestStatus::QUEUED);

        $slotRepo = Mockery::mock(ChargingSlotRepository::class);
        $slotRepo->shouldReceive('findAvailableSlot')->once()->andReturn(null);

        $assignSlot = new AssignSlotToRequest($slotRepo);
        $assignSlot($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::QUEUED)
            ->and($chargingRequest->slot_id)->toBeNull();
    });
});
