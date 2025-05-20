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
use Tests\TestCase;

/**
 * @throws UserAlreadyHasActiveChargingRequest
 */
describe('Unit: Start Charging Request', function (): void {
    it('accepts a user charging request and attempts assignment', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = new ChargingWindow(CarbonImmutable::now(), CarbonImmutable::now()->addHour());
        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof ChargingRequest))
            ->andReturnUsing(fn (ChargingRequest $request) => $request);

        /** @var MockInterface&AssignSlotToRequest $assignSlot */
        $assignSlot = $this->makeMock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof ChargingRequest));

        $eligibility = $this->mockEligibility(fn (MockInterface $mock) => $mock->shouldReceive('ensureUserCanStart')->once()->with($user));

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
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();

        $chargingWindow = new ChargingWindow(
            CarbonImmutable::now(),
            CarbonImmutable::now()->addHours(3)
        );

        $batteryPercentage = new BatteryPercentage(25);

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);
        $repository->shouldReceive('save')->once()
            ->andReturnUsing(fn (ChargingRequest $request) => $request);

        $assignSlot = Mockery::mock(AssignSlotToRequest::class);
        $assignSlot->shouldReceive('__invoke')->once()
            ->with(Mockery::on(fn (ChargingRequest $request) => $request->status === ChargingRequestStatus::QUEUED));

        $eligibility = $this->mockEligibility(fn (MockInterface $eligibility) => $eligibility->shouldReceive('ensureUserCanStart')->once()->with($user));

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
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $batteryPercentage = new BatteryPercentage(25);
        $chargingWindow = $this->createWindow('16-05-2025 09:00', '16-05-2025 14:00');

        /** @var MockInterface&ChargingRequestRepository $repository */
        $repository = $this->makeMock(ChargingRequestRepository::class);

        /** @var MockInterface&AssignSlotToRequest $assignSlot */
        $assignSlot = $this->makeMock(AssignSlotToRequest::class);

        /** @var MockInterface&ChargingRequestEligibility $eligibility */
        $eligibility = $this->mockEligibility(fn (MockInterface $mock) => $mock->shouldReceive('ensureUserCanStart')->once()->with($user)
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
