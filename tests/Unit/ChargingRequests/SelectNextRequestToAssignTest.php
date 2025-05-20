<?php

declare(strict_types=1);

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\Write\SelectNextRequestToAssign;
use Tests\TestCase;

describe('Unit: Select Next Request to Assign', function (): void {
    it('selects the next charging request to assign based on urgency and battery needs', function (): void {
        /** @var TestCase $this */
        $lowPriorityRequest = new ChargingRequest();
        $lowPriorityRequest->starts_at = $this->parseCarbon('19-05-2025 13:00');
        $lowPriorityRequest->battery_percentage = 50;

        $midPriorityRequest = new ChargingRequest();
        $midPriorityRequest->starts_at = $this->parseCarbon('19-05-2025 11:00');
        $midPriorityRequest->battery_percentage = 35;

        $highPriorityRequest = new ChargingRequest();
        $highPriorityRequest->starts_at = $this->parseCarbon('19-05-2025 11:00');
        $highPriorityRequest->battery_percentage = 10;

        $useCase = new SelectNextRequestToAssign();
        $nextRequestToAssign = $useCase(collect([$lowPriorityRequest, $midPriorityRequest, $highPriorityRequest]));

        expect($nextRequestToAssign)->toBe($highPriorityRequest);
    });

    it('returns null if the queue is empty', function (): void {
        $useCase = new SelectNextRequestToAssign();
        $nextRequestToAssign = $useCase(collect());

        expect($nextRequestToAssign)->toBeNull();
    });
});
