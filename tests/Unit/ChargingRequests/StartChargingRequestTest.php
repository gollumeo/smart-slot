<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingRequests\Write\AssignSlotToRequest;
use App\ChargingRequests\Write\StartChargingRequest;
use App\Contracts\ChargingRequestRepository;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use App\Policies\ChargingRequestEligibility;
use Carbon\CarbonImmutable;
use Mockery\MockInterface;

/**
 * @throws UserAlreadyHasActiveChargingRequest
 */
describe('Unit: Start Charging Request', function (): void {
    it('accepts a user charging request and attempts assignment', function (): void {
        $user = $this->createStaticTestUser();

        $chargingWindow = new ChargingWindow(CarbonImmutable::now(), CarbonImmutable::now()->addHour());
        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = mockRepository();
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof ChargingRequest))
            ->andReturnUsing(fn ($request) => $request);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(fn (ChargingRequest $request) => $request instanceof ChargingRequest));

        $eligibility = mockEligibility(fn ($m) => $m->shouldReceive('ensureUserCanStart')->once()->with($user));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlot: $assignSlot,
            eligibility: $eligibility
        );

        $chargingRequest = $useCase->execute(
            user: $user,
            chargingWindow: $chargingWindow,
            batteryPercentage: $batteryPercentage
        );

        expect($chargingRequest->status)->not()->toBeNull();
    });

    it('keeps the request in queue when no slot is available', function (): void {
        $user = $this->createStaticTestUser();

        $chargingWindow = new ChargingWindow(
            CarbonImmutable::now(),
            CarbonImmutable::now()->addHours(3)
        );

        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = mockRepository();
        $repository->shouldReceive('save')->once()
            ->andReturnUsing(fn (ChargingRequest $request) => $request);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')->once()
            ->with(Mockery::on(fn (ChargingRequest $request) => $request->status === ChargingRequestStatus::QUEUED));

        $eligibility = mockEligibility(fn (MockInterface $eligibility) => $eligibility->shouldReceive('ensureUserCanStart')->once()->with($user));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlot: $assignSlot,
            eligibility: $eligibility
        );

        $chargingRequest = $useCase->execute(
            user: $user,
            chargingWindow: $chargingWindow,
            batteryPercentage: $batteryPercentage
        );

        expect($chargingRequest->status)->toBe(ChargingRequestStatus::QUEUED);
    });

    it('rejects a charging request when the user already has one in progress', function (): void {
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(25);
        $chargingWindow = new ChargingWindow(
            CarbonImmutable::createFromFormat('d-m-Y H:i', '16-05-2025 09:00'),
            CarbonImmutable::createFromFormat('d-m-Y H:i', '16-05-2025 14:00')
        );

        $repository = mockRepository();
        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $eligibility = mockEligibility(fn ($m) => $m->shouldReceive('ensureUserCanStart')->once()->with($user)
            ->andThrow(UserAlreadyHasActiveChargingRequest::class));

        $useCase = new StartChargingRequest(
            repository: $repository,
            assignSlot: $assignSlot,
            eligibility: $eligibility
        );

        expect(fn () => $useCase->execute(
            user: $user,
            chargingWindow: $chargingWindow,
            batteryPercentage: $batteryPercentage
        ))->toThrow(UserAlreadyHasActiveChargingRequest::class);
    });
});

function mockRepository(): ChargingRequestRepository
{
    return Mockery::mock(ChargingRequestRepository::class);
}

function mockEligibility(callable $expectations): ChargingRequestEligibility
{
    $mock = Mockery::mock(ChargingRequestEligibility::class);
    $expectations($mock);

    return $mock;
}
