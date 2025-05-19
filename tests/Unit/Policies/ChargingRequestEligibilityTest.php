<?php

declare(strict_types=1);

use App\Contracts\ChargingRequestRepository;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use App\Policies\ChargingRequestEligibility;
use App\Users\User;

describe('Unit: Charging Request Eligibility', function (): void {
    it('throws if user has already an active request', function (): void {
        $repository = Mockery::mock(ChargingRequestRepository::class);
        $repository->shouldReceive('hasActiveRequestFor')->once()->andReturnTrue();

        $eligibility = new ChargingRequestEligibility($repository);
        $user = new User();

        expect(fn () => $eligibility->ensureUserCanStart($user))->toThrow(UserAlreadyHasActiveChargingRequest::class);
    });

    it('passes if user has no active request', function (): void {
        $repository = Mockery::mock(ChargingRequestRepository::class);
        $repository->shouldReceive('hasActiveRequestFor')->once()->andReturnFalse();

        $eligibility = new ChargingRequestEligibility($repository);
        $user = new User();

        $eligibility->ensureUserCanStart($user);
        // no throw === success
        expect(true)->toBeTrue();
    });
});
