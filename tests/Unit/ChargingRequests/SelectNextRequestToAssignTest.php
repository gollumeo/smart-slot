<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\Write\SelectNextRequestToAssign;
use Carbon\CarbonImmutable;

describe('Unit: Select Next Request to Assign', function (): void {
    it('selects the next charging request to assign based on urgency and battery needs', function (): void {
        $lowPriorityRequest = new ChargingRequest();
        $lowPriorityRequest->starts_at = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 13:00');
        $lowPriorityRequest->battery_percentage = 50;

        $midPriorityRequest = new ChargingRequest();
        $midPriorityRequest->starts_at = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 11:00');
        $midPriorityRequest->battery_percentage = 35;

        $highPriorityRequest = new ChargingRequest();
        $highPriorityRequest->starts_at = CarbonImmutable::createFromFormat('d-m-Y H:i', '19-05-2025 11:00');
        $highPriorityRequest->battery_percentage = 10;

        $useCase = new SelectNextRequestToAssign();
        $nextRequestToAssign = $useCase(collect([$lowPriorityRequest, $midPriorityRequest, $highPriorityRequest]));

        expect($nextRequestToAssign)->toBe($highPriorityRequest);

    });
});
