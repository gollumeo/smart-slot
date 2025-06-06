<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\Write\MarkChargingRequestAsStarted;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use Carbon\CarbonImmutable;
use Tests\TestCase;

describe('Unit: Mark Charging Request As Started', function (): void {
    it('starts a charging request that is assigned to a slot', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(35);
        $chargingWindow = $this->createWindow('20-05-2025 12:00', '20-05-2025 16:00');

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: $batteryPercentage,
            chargingWindow: $chargingWindow
        );
        $chargingRequest->slot_id = 42;

        $chargingRequest->markAs(ChargingRequestStatus::ASSIGNED);

        $useCase = new MarkChargingRequestAsStarted();
        $useCase->execute($chargingRequest);

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::CHARGING);
    });

    it('ensures charging request is marked as assigned before starting the charge', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(35);
        $chargingWindow = $this->createWindow('20-05-2025 12:00', '20-05-2025 16:00');

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: $batteryPercentage,
            chargingWindow: $chargingWindow
        );
        $chargingRequest->slot_id = 42;

        $useCase = new MarkChargingRequestAsStarted();

        expect(fn () => $useCase->execute($chargingRequest))->toThrow(CannotStartChargingRequest::class);
    });

    it('cannot mark a terminal request as charging', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(50),
            chargingWindow: $this->createWindow('21-05-2025 12:00', '21-05-2025 16:00'),
            status: ChargingRequestStatus::DONE
        );

        $useCase = new MarkChargingRequestAsStarted();

        expect(fn () => $useCase->execute($chargingRequest))->toThrow(ChargingRequestAlreadyFinished::class);
    });

    it('records the charging start time', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(50),
            chargingWindow: $this->createWindow('21-05-2025 12:00', '21-05-2025 16:00'),
            status: ChargingRequestStatus::ASSIGNED
        );

        $useCase = new MarkChargingRequestAsStarted();

        $useCase->execute($chargingRequest);

        expect($chargingRequest->charging_started_at)->not()->toBeNull()
            ->and($chargingRequest->charging_started_at?->isSameMinute(CarbonImmutable::now()))->toBeTrue();
    });
});
