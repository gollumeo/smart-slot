<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingSlotRepository;
use Mockery\MockInterface;
use Tests\TestCase;

describe('Unit: Assign Slot To Charging Request', function (): void {
    it('confirms the user\'s request by assigning it to an available charging slot', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(25);

        $chargingWindow = $this->createWindow('19-05-2025 09:00', '19-05-2025 17:00');

        $chargingRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow);

        $slot = new ChargingSlot();
        $slot->id = 42;

        /** @var MockInterface&ChargingSlotRepository $slotRepository */
        $slotRepository = $this->makeMock(ChargingSlotRepository::class);
        $slotRepository->shouldReceive('findAvailableSlot')->once()->andReturn($slot);

        $assignSlot = new AssignSlotToRequest($slotRepository);
        $assignSlot($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::ASSIGNED)
            ->and($chargingRequest->slot_id)->toBe(42);
    });

    it('places the request in waiting line when all charging spots are occupied', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(50);

        $chargingWindow = $this->createWindow('19-05-2025 13:00', '19-05-2025 17:00');

        $chargingRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow);

        $slotRepo = $this->makeMock(ChargingSlotRepository::class);
        $slotRepo->shouldReceive('findAvailableSlot')->once()->andReturn(null);

        $assignSlot = new AssignSlotToRequest($slotRepo);
        $assignSlot($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::QUEUED)
            ->and($chargingRequest->slot_id)->toBeNull();
    });
});
