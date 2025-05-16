<?php

declare(strict_types=1);

use App\ChargingRequests\ValueObjects\BatteryPercentage;

describe('Unit: Battery Percentage', function (): void {
    it('accepts 0 as a valid percentage', function (): void {
        $batteryPercentage = new BatteryPercentage(0);
        expect($batteryPercentage->value())->toBe(0.0);
    });

    it('accepts 100 as a valid percentage', function (): void {
        $batteryPercentage = new BatteryPercentage(100);
        expect($batteryPercentage->value())->toBe(100.00);
    });

    it('throws if percentage is less than 0', function (): void {
        expect(fn () => new BatteryPercentage(-1))->toThrow(InvalidArgumentException::class);
    });

    it('throws if percentage is greater than 100', function (): void {
        expect(fn () => new BatteryPercentage(101))->toThrow(InvalidArgumentException::class);
    });
});
