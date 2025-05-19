<?php

declare(strict_types=1);

use App\ChargingRequests\ValueObjects\ChargingWindow;
use Carbon\CarbonImmutable;

describe('Unit: Charging Window', function (): void {
    it('throws if it ends before it starts', function (): void {
        $start = CarbonImmutable::now();
        $end = $start->subDay();
        expect(fn () => new ChargingWindow(startTime: $start, endTime: $end))->toThrow(InvalidArgumentException::class);
    });

    it('throws if it starts as it ends', function (): void {
        $now = CarbonImmutable::now();
        expect(fn () => new ChargingWindow(startTime: $now, endTime: $now))->toThrow(InvalidArgumentException::class);
    });

    it('accepts a valid interval', function (): void {
        $start = CarbonImmutable::createFromFormat('d-m-Y H:i', '16-05-2025 14:00', 'Europe/Brussels');
        $end = CarbonImmutable::createFromFormat('d-m-Y H:i', '16-05-2025 17:00', 'Europe/Brussels');

        $chargingWindow = new ChargingWindow($start, $end);

        expect($chargingWindow->start())->toBe($start)
            ->and($chargingWindow->end())->toBe($end);
    });
});
