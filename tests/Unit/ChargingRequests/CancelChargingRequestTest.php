<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingRequests\Write\CancelChargingRequest;
use App\ChargingRequests\Write\SelectNextRequestToAssign;
use App\Contracts\ChargingRequestRepository;
use Mockery\MockInterface;
use Tests\TestCase;

describe('Unit: Cancel Charging Request', function (): void {
    it('cancels a queued request', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(50),
            chargingWindow: $this->createWindow('21-05-2025 09:00', '21-05-2025 12:00'),
        );

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')->once()->with($chargingRequest);

        /** @var MockInterface&AssignSlotToRequest $assignSlot */
        $assignSlot = $this->makeMock(AssignSlotToRequest::class);
        $assignSlot->shouldNotReceive('__invoke');

        /** @var MockInterface&SelectNextRequestToAssign $selectNextRequest */
        $selectNextRequest = $this->makeMock(SelectNextRequestToAssign::class);
        $selectNextRequest->shouldNotReceive('__invoke');

        $useCase = new CancelChargingRequest($repository, $assignSlot, $selectNextRequest);

        $useCase->execute($chargingRequest);
        expect($chargingRequest->status)->toBe(ChargingRequestStatus::CANCELLED);
    });

    it('cancels an assigned request and reassigns the slot', function (): void {
        // TODO
    });

    it('cannot cancel a terminal request', function (): void {
        // TODO
    });
});
