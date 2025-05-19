<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingRequests\Write\EndChargingRequest;
use App\ChargingRequests\Write\SelectNextRequestToAssign;
use App\Contracts\ChargingRequestRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

describe('Unit: End Charging Request', function (): void {
    it('finalizes a charging request and releases the occupied charging slot', function (): void {
        $user = $this->createStaticTestUser();

        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 12:00');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 15:00');
        $chargingWindow = new ChargingWindow($start, $end);

        $batteryPercentage = new BatteryPercentage(75);

        $chargingRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow, ChargingRequestStatus::QUEUED);
        $chargingRequest->slot_id = 42;

        $repository = Mockery::mock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')->once()->with($chargingRequest);
        $repository->shouldReceive('getPendingRequests')->once()->andReturn(collect());

        $selectNextRequest = Mockery::mock(SelectNextRequestToAssign::class);
        $selectNextRequest->shouldReceive('__invoke')->once()
            ->with(Mockery::type(Collection::class))
            ->andReturn(null);
        $useCase = new EndChargingRequest(
            chargingRequests: $repository,
            assignSlot: Mockery::mock(AssignSlotToRequest::class),
            selectNextRequest: $selectNextRequest,
        );

        $useCase($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::DONE);
    });

    it('cannot end a request which is already terminale', function (): void {
        $user = $this->createStaticTestUser();

        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 12:00');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 15:00');
        $chargingWindow = new ChargingWindow($start, $end);

        $batteryPercentage = new BatteryPercentage(75);

        $doneRequest = ChargingRequest::fromDomain($user->id, $batteryPercentage, $chargingWindow, ChargingRequestStatus::DONE);

        $repository = Mockery::mock(ChargingRequestRepository::class);
        $repository->shouldNotReceive('save');

        $useCase = new EndChargingRequest(
            chargingRequests: $repository,
            assignSlot: Mockery::mock(AssignSlotToRequest::class),
            selectNextRequest: Mockery::mock(SelectNextRequestToAssign::class),
        );

        expect(fn () => $useCase($doneRequest))->toThrow(LogicException::class);
    });

    it('assigns the freed slot to the next queued request, if one exists', function (): void {
        $user = $this->createStaticTestUser();

        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 08:00');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 12:00');

        $finishedRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(50),
            chargingWindow: new ChargingWindow($start, $end),
            status: ChargingRequestStatus::ASSIGNED
        );
        $finishedRequest->slot_id = 42;

        $highPriorityRequest = new ChargingRequest();
        $highPriorityRequest->starts_at = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 13:00');
        $highPriorityRequest->battery_percentage = 20;

        $lowPriorityRequest = new ChargingRequest();
        $lowPriorityRequest->starts_at = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 13:00');
        $lowPriorityRequest->battery_percentage = 80;

        $chargingRepository = Mockery::mock(ChargingRequestRepository::class);
        $chargingRepository->shouldReceive('save')->once()->with($finishedRequest);
        $chargingRepository->shouldReceive('getPendingRequests')->once()->andReturn(collect([$highPriorityRequest, $lowPriorityRequest]));

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')->once()->with($highPriorityRequest);

        $selectNextRequest = Mockery::mock(SelectNextRequestToAssign::class);
        $selectNextRequest->shouldReceive('__invoke')->once()
            ->with(Mockery::on(fn (Collection $collection) => $collection->contains($highPriorityRequest)))
            ->andReturn($highPriorityRequest);

        $useCase = new EndChargingRequest(
            chargingRequests: $chargingRepository,
            assignSlot: $assignSlot,
            selectNextRequest: $selectNextRequest
        );

        $useCase($finishedRequest);
    });

    it('does nothing when no request is selected', function (): void {
        $user = $this->createStaticTestUser();

        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 12:00');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 15:00');
        $chargingWindow = new ChargingWindow($start, $end);

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(40),
            chargingWindow: $chargingWindow,
            status: ChargingRequestStatus::ASSIGNED
        );
        $chargingRequest->slot_id = 42;

        $repository = Mockery::mock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')->once()->with($chargingRequest);
        $repository->shouldReceive('getPendingRequests')->once()->andReturn(collect());

        $selector = Mockery::mock(SelectNextRequestToAssign::class);
        $selector->shouldReceive('__invoke')->once()
            ->with(Mockery::type(Collection::class))
            ->andReturn(null);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldNotReceive('__invoke');

        $useCase = new EndChargingRequest(
            chargingRequests: $repository,
            assignSlot: $assignSlot,
            selectNextRequest: $selector,
        );

        $useCase($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::DONE);
    });
});
