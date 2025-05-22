<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingRequests\Write\EndChargingRequest;
use App\ChargingRequests\Write\SelectNextRequestToAssign;
use App\ChargingSlots\ChargingSlot;
use App\Contracts\ChargingRequestRepository;
use App\Contracts\ChargingSlotRepository;
use App\Exceptions\ChargingRequestAlreadyFinished;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

describe('Unit: End Charging Request', function (): void {
    it('finalizes a charging request and releases the occupied charging slot', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(75);
        $chargingWindow = $this->createWindow('19-05-2025 12:00', '19-05-2025 15:00');

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: $batteryPercentage,
            chargingWindow: $chargingWindow
        );
        $chargingRequest->slot_id = 42;

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')->once()->with($chargingRequest);
        $repository->shouldReceive('getPendingRequests')->once()->andReturn(collect());

        /** @var MockInterface&SelectNextRequestToAssign $selectNextRequest */
        $selectNextRequest = $this->makeMock(SelectNextRequestToAssign::class);
        $selectNextRequest->shouldReceive('__invoke')->once()
            ->with(Mockery::type(Collection::class))
            ->andReturn(null);

        /** @var MockInterface&ChargingSlotRepository $slots */
        $slots = $this->mock(ChargingSlotRepository::class);

        $useCase = new EndChargingRequest(
            chargingRequests: $repository,
            slots: $slots,
            selectNextRequest: $selectNextRequest,
        );

        $useCase->execute($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::DONE);
    });

    it('cannot end a request which is already terminale', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = $this->createWindow('19-05-2025 12:00', '19-05-2025 15:00');

        $batteryPercentage = new BatteryPercentage(75);

        $doneRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow, ChargingRequestStatus::DONE);

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);
        $repository->shouldNotReceive('save');

        /** @var MockInterface&AssignSlotToRequest $assignSlot */
        $assignSlot = $this->makeMock(AssignSlotToRequest::class);

        /** @var MockInterface&SelectNextRequestToAssign $selectNextRequest */
        $selectNextRequest = $this->makeMock(SelectNextRequestToAssign::class);

        /** @var MockInterface&ChargingSlotRepository $slots */
        $slots = $this->mock(ChargingSlotRepository::class);

        $useCase = new EndChargingRequest(
            chargingRequests: $repository,
            slots: $slots,
            selectNextRequest: $selectNextRequest,
        );

        expect(fn () => $useCase->execute($doneRequest))->toThrow(ChargingRequestAlreadyFinished::class);
    });

    it('assigns the freed slot to the next queued request, if one exists', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = $this->createWindow('19-05-2025 08:00', '19-05-2025 12:00');

        $finishedRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(50),
            chargingWindow: $chargingWindow,
            status: ChargingRequestStatus::ASSIGNED
        );
        $finishedRequest->slot_id = 42;

        $highPriorityRequest = new ChargingRequest();
        $highPriorityRequest->starts_at = $this->parseCarbon('19-05-2025 13:00');
        $highPriorityRequest->battery_percentage = 20;
        $highPriorityRequest->ends_at = $this->parseCarbon('19-05-2025 15:00');
        $highPriorityRequest->status = ChargingRequestStatus::QUEUED;

        $lowPriorityRequest = new ChargingRequest();
        $lowPriorityRequest->starts_at = $this->parseCarbon('19-05-2025 13:00');
        $lowPriorityRequest->ends_at = $this->parseCarbon('19-05-2025 15:00');
        $lowPriorityRequest->battery_percentage = 80;
        $lowPriorityRequest->status = ChargingRequestStatus::QUEUED;

        /** @var MockInterface&ChargingRequestRepository $chargingRepository */
        $chargingRepository = $this->makeMock(ChargingRequestRepository::class);
        $chargingRepository->shouldReceive('save')->once()->with($finishedRequest);
        $chargingRepository->shouldReceive('getPendingRequests')->once()->andReturn(collect([$highPriorityRequest, $lowPriorityRequest]));

        /** @var MockInterface&SelectNextRequestToAssign $selectNextRequest */
        $selectNextRequest = $this->makeMock(SelectNextRequestToAssign::class);
        $selectNextRequest->shouldReceive('__invoke')->once()
            ->with(Mockery::on(fn (Collection $collection) => $collection->contains($highPriorityRequest)))
            ->andReturn($highPriorityRequest);

        /** @var MockInterface&ChargingSlotRepository $slots */
        $slots = $this->mock(ChargingSlotRepository::class);

        $slot = ChargingSlot::factory()->make(['id' => 42]);

        $slots->shouldReceive('list')->once()->andReturn(collect([$slot]));

        $useCase = new EndChargingRequest(
            chargingRequests: $chargingRepository,
            slots: $slots,
            selectNextRequest: $selectNextRequest
        );

        $useCase->execute($finishedRequest);

        expect($finishedRequest->status)->toBe(ChargingRequestStatus::DONE);
    });

    it('does nothing when no request is selected', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = $this->createWindow('19-05-2025 12:00', '19-05-2025 15:00');

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(40),
            chargingWindow: $chargingWindow,
            status: ChargingRequestStatus::ASSIGNED
        );
        $chargingRequest->slot_id = 42;

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')->once()->with($chargingRequest);
        $repository->shouldReceive('getPendingRequests')->once()->andReturn(collect());

        /** @var MockInterface&SelectNextRequestToAssign $selectNextRequest */
        $selectNextRequest = $this->makeMock(SelectNextRequestToAssign::class);
        $selectNextRequest->shouldReceive('__invoke')->once()
            ->with(Mockery::type(Collection::class))
            ->andReturn(null);

        /** @var MockInterface&ChargingSlotRepository $slots */
        $slots = $this->mock(ChargingSlotRepository::class);

        $useCase = new EndChargingRequest(
            chargingRequests: $repository,
            slots: $slots,
            selectNextRequest: $selectNextRequest,
        );

        $useCase->execute($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::DONE);
    });
});
