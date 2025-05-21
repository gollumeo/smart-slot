<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingSlots\ChargingSlot;
use Tests\TestCase;

describe('Unit: Charging Request Model', function (): void {
    it('creates a charging request from domain data', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $chargingRequest = ChargingRequest::fromDomain(
            userId: $user->id,
            batteryPercentage: new BatteryPercentage(50),
            chargingWindow: $this->createWindow('21-05-2025 09:00', '21-05-2025 11:00')
        );

        expect($chargingRequest)->toBeInstanceOf(ChargingRequest::class)
            ->and($chargingRequest->toArray())->toHaveKeys([
                'user_id',
                'battery_percentage',
                'starts_at',
                'ends_at',
                'status',
            ])
            ->and($chargingRequest->status)->toBe(ChargingRequestStatus::QUEUED);
    });

    it('throws when trying to mark a request as ASSIGNED without a slot', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $request = ChargingRequest::fromDomain(
            $user->id,
            new BatteryPercentage(30),
            $this->createWindow('21-05-2025 10:00', '21-05-2025 14:00')
        );

        expect(fn () => $request->markAs(ChargingRequestStatus::ASSIGNED))
            ->toThrow(App\Exceptions\CannotAssignRequestWithoutSlot::class);
    });

    it('assigns a slot and marks the request as ASSIGNED', function (): void {
        /** @var TestCase $this */
        $user = $this->createStaticTestUser();
        $request = ChargingRequest::fromDomain(
            $user->id,
            new BatteryPercentage(60),
            $this->createWindow('22-05-2025 08:00', '22-05-2025 12:00')
        );

        $slot = new ChargingSlot();
        $slot->id = 7;

        $request->assignTo($slot);

        expect($request->slot_id)->toBe(7)
            ->and($request->status)->toBe(ChargingRequestStatus::ASSIGNED);
    });

    it('returns the correct ChargingWindow value object', function (): void {
        /** @var TestCase $this */
        $window = $this->createWindow('22-05-2025 10:00', '22-05-2025 13:00');

        $request = ChargingRequest::fromDomain(
            userId: 1,
            batteryPercentage: new BatteryPercentage(40),
            chargingWindow: $window
        );

        $vo = $request->chargingWindow();

        expect($vo->start())->toEqual($window->start())
            ->and($vo->end())->toEqual($window->end());
    });

    it('detects time window conflicts with another charging window', function (): void {
        /** @var TestCase $this */
        $window = $this->createWindow('22-05-2025 09:00', '22-05-2025 12:00');
        $request = ChargingRequest::fromDomain(1, new BatteryPercentage(70), $window);

        $conflicting = $this->createWindow('22-05-2025 11:00', '22-05-2025 13:00');
        $nonConflicting = $this->createWindow('22-05-2025 12:30', '22-05-2025 13:30');

        expect($request->conflictsWith($conflicting))->toBeTrue()
            ->and($request->conflictsWith($nonConflicting))->toBeFalse();
    });

});
