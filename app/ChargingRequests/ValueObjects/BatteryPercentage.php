<?php

declare(strict_types=1);

namespace App\ChargingRequests\ValueObjects;

use InvalidArgumentException;

final class BatteryPercentage
{
    public function __construct(private float $percentage)
    {
        if ($this->percentage < 0 || $this->percentage > 100) {
            throw new InvalidArgumentException('Percentage must be between 0 and 100.');
        }

        $this->percentage = round($this->percentage, 2);
    }

    public function value(): float
    {
        return $this->percentage;
    }
}
