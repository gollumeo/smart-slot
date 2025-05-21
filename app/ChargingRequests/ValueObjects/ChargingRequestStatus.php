<?php

declare(strict_types=1);

namespace App\ChargingRequests\ValueObjects;

enum ChargingRequestStatus: string
{
    case QUEUED = 'queued';
    case ASSIGNED = 'assigned';
    case CHARGING = 'charging';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    public function isTerminal(): bool
    {
        return in_array($this, [self::DONE, self::CANCELLED]);
    }

    public function isAssigned(): bool
    {
        return $this === self::ASSIGNED;
    }
}
