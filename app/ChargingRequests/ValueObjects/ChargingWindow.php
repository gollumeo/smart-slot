<?php

declare(strict_types=1);

namespace App\ChargingRequests\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class ChargingWindow
{
    public function __construct(
        private CarbonImmutable $startTime,
        private CarbonImmutable $endTime
    ) {
        if ($endTime->lessThanOrEqualTo($startTime)) {
            throw new InvalidArgumentException('The charging window must end after it starts.');
        }
    }

    public function start(): CarbonImmutable
    {
        return $this->startTime;
    }

    public function end(): CarbonImmutable
    {
        return $this->endTime;
    }
}
