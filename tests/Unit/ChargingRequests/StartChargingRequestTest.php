<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingRequests\Write\StartChargingRequest;
use App\Contracts\RepositoryContract;
use App\Users\User;
use Carbon\CarbonImmutable;
use Mockery\MockInterface;

describe('Unit: Start Charging Request', function () {
    it('assigns a request to an available slot immediately', function () {
        $user = User::register('Pierre', 'pierre@izix.eu', 'secret');
        $user->save();

        $chargingWindow = new ChargingWindow(CarbonImmutable::now(), CarbonImmutable::now()->addHour());
        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&RepositoryContract $repository */
        $repository = mockRepository();
        $expectation = $repository->shouldReceive('hasActiveRequestFor');
        $expectation->with($user)->andReturnFalse();

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')->once()->with(Mockery::on(fn ($request) => $request instanceof ChargingRequest));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlot: $assignSlot
        );

        $chargingRequest = $useCase->execute(
            user: $user,
            chargingWindow: $chargingWindow,
            batteryPercentage: $batteryPercentage
        );

        expect($chargingRequest)->toBe(ChargingRequestStatus::QUEUED);
    });

    it('places the charging request in a waiting line if no slot is available', function () {
        // TODO
    });

    it('rejects a new charging request if the user already has an active one', function () {
        // TODO
    });
});

function mockRepository(): RepositoryContract
{
    /** @var MockInterface&RepositoryContract $mock */
    $mock = Mockery::mock(RepositoryContract::class);

    return $mock;
}
