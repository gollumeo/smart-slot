<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\Contracts\RepositoryContract;
use App\Users\User;

describe('Unit: Start Charging Request', function () {
    it('assigns a request to an available slot immediately', function () {
        $user = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('secret'),
        ]);
        $user->save();

        $chargingWindow = new ChargingWindow(Carbon::now(), Carbon::now()->addHour());
        $batteryPercentage = new BatteryPercentage(25);

        $repository = Mockery::mock(RepositoryContract::class);
        $repository->shouldReceive('hasActiveRequestFor')->with($user)->andReturnFalse();
        $repository->shouldReceive('save')->andReturnUsing(fn ($request) => $request);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')->once()->with(Mockery::on(fn ($request) => $request instanceof ChargingRequest));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlotToRequest: $assignSlot
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
