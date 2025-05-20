<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use Illuminate\Support\Collection;

class SelectNextRequestToAssign
{
    /**
     * @param Collection<int, ChargingRequest> $queuedRequests
     * @return ChargingRequest|null
     */
    public function __invoke(Collection $queuedRequests): ?ChargingRequest
    {
        return $queuedRequests
            ->sort(function (ChargingRequest $a, ChargingRequest $b) {
                if ($a->starts_at->lt($b->starts_at)) {
                    return -1;
                }
                if ($a->starts_at->gt($b->starts_at)) {
                    return 1;
                }

                return $a->battery_percentage <=> $b->battery_percentage;
            })
            ->values()
            ->first();
    }
}
