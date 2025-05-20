<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingSlotRepository;
use App\Contracts\SlotAvailabilityRules;
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
        $slotRepository->shouldReceive('list')->once()->andReturn(collect([$slot]));

        /** @var MockInterface&SlotAvailabilityRules $slotAvailability */
        $slotAvailability = $this->makeMock(SlotAvailabilityRules::class);
        $slotAvailability->shouldReceive('isAvailable')->once()
            ->with(Mockery::on(fn (ChargingSlot $mock) => $mock->id === $slot->id), Mockery::type(ChargingWindow::class))
            ->andReturnTrue();

        $assignSlot = new AssignSlotToRequest($slotRepository, $slotAvailability);
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

        $slot = new ChargingSlot();
        $slot->id = 999;

        /** @var MockInterface&ChargingSlotRepository $slotRepository */
        $slotRepository = $this->makeMock(ChargingSlotRepository::class);
        $slotRepository->shouldReceive('list')->once()->andReturn(collect([$slot]));

        /** @var MockInterface&SlotAvailabilityRules $slotAvailability */
        $slotAvailability = $this->makeMock(SlotAvailabilityRules::class);
        $slotAvailability->shouldReceive('isAvailable')->once()
            ->with(Mockery::on(fn (ChargingSlot $mock) => $mock->id === $slot->id), Mockery::type(ChargingWindow::class))
            ->andReturnFalse();

        $assignSlot = new AssignSlotToRequest($slotRepository, $slotAvailability);
        $assignSlot($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::QUEUED)
            ->and($chargingRequest->slot_id)->toBeNull();
    });

    it('assigns the charging request to the first available slot within its charging window', function (): void {
        /** @var TestCase $this */
        $firstSlot = new ChargingSlot();
        $firstSlot->id = 42;

        $secondSlot = new ChargingSlot();
        $secondSlot->id = 11;

        $thirdSlot = new ChargingSlot();
        $thirdSlot->id = 25;

        $user = $this->createStaticTestUser();
        $chargingWindow = $this->createWindow('22-05-2025 09:00', '22-05-2025 11:00');
        $batteryPercentage = new BatteryPercentage(30);

        $requestToAssign = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow);

        /** @var MockInterface&ChargingSlotRepository $slotRepository */
        $slotRepository = $this->makeMock(ChargingSlotRepository::class);
        $slotRepository->shouldReceive('list')->once()->andReturn(collect([$firstSlot, $secondSlot, $thirdSlot]));

        /** @var MockInterface&SlotAvailabilityRules $slotAvailability */
        $slotAvailability = $this->makeMock(SlotAvailabilityRules::class);
        $slotAvailability->shouldReceive('isAvailable')->once()->with(
            Mockery::on(fn (ChargingSlot $slot) => $slot->id === $firstSlot->id),
            Mockery::on(fn (ChargingWindow $window) => $window->start()->equalTo($chargingWindow->start()) && $window->end()->equalTo($chargingWindow->end()))
        )->andReturnFalse();

        $slotAvailability->shouldReceive('isAvailable')->once()->with(
            Mockery::on(fn (ChargingSlot $slot) => $slot->id === $secondSlot->id),
            Mockery::on(fn (ChargingWindow $window) => $window->start()->equalTo($chargingWindow->start()) && $window->end()->equalTo($chargingWindow->end()))
        )->andReturnTrue();

        $useCase = new AssignSlotToRequest($slotRepository, $slotAvailability);
        $useCase($requestToAssign);

        expect($requestToAssign->slot_id)->toBe($secondSlot->id)
            ->and($requestToAssign->status)->toBe(ChargingRequestStatus::ASSIGNED);
    });
});
