<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingRequests\Write\StartChargingRequest;
use App\Contracts\RepositoryContract;
use Carbon\CarbonImmutable;
use Mockery\MockInterface;

describe('Unit: Start Charging Request', function (): void {
    it('accepts a user charging request and attempts assignment', function (): void {
        $user = $this->createTestUser();

        $chargingWindow = new ChargingWindow(CarbonImmutable::now(), CarbonImmutable::now()->addHour());
        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&RepositoryContract $repository */
        $repository = mockRepository();
        $expectation = $repository->shouldReceive('hasActiveRequestFor');
        $expectation->with($user)->andReturnFalse();

        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof ChargingRequest))
            ->andReturnUsing(fn ($request) => $request);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')->once()->with(Mockery::on(fn (ChargingRequest $request) => $request instanceof ChargingRequest));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlot: $assignSlot
        );

        $chargingRequest = $useCase->execute(
            user: $user,
            chargingWindow: $chargingWindow,
            batteryPercentage: $batteryPercentage
        );

        expect($chargingRequest->status)->not()->toBeNull();
    });

    it('keeps the request in queue when no slot is available', function (): void {
        $user = $this->createTestUser();

        $chargingWindow = new ChargingWindow(
            CarbonImmutable::now(),
            CarbonImmutable::now()->addHours(3)
        );

        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&RepositoryContract $repository */
        $repository = mockRepository();
        $repository->shouldReceive('hasActiveRequestFor')->with($user)->andReturnFalse();
        $repository->shouldReceive('save')->once()->andReturnUsing(fn (ChargingRequest $request) => $request);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);

        $assignSlot->shouldReceive('__invoke')->once()
            ->with(Mockery::on(function (ChargingRequest $request) {
                return $request->status === ChargingRequestStatus::QUEUED;
            }));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlot: $assignSlot
        );

        $chargingRequest = $useCase->execute(
            user: $user,
            chargingWindow: $chargingWindow,
            batteryPercentage: $batteryPercentage
        );

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::QUEUED);
    });

    it('rejects a charging request when the user already has one in progress', function () {
        $user = $this->createTestUser();

        // TODO
    });
});

function mockRepository(): RepositoryContract
{
    /** @var MockInterface&RepositoryContract $mock */
    $mock = Mockery::mock(RepositoryContract::class);

    return $mock;
}
