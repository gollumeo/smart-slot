<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\EndChargingRequest;
use App\Contracts\ChargingRequestRepository;
use Carbon\CarbonImmutable;

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

        $useCase = new EndChargingRequest($repository);
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
        $repository->shouldReceive('save')->once()->with($doneRequest);

        $useCase = new EndChargingRequest($repository);

        expect(fn () => $useCase($doneRequest))->toThrow(LogicException::class);
    });
});
