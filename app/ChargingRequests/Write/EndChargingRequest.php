<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\Contracts\ChargingRequestRepository;
use LogicException;

final class EndChargingRequest
{
    public function __construct(private readonly ChargingRequestRepository $chargingRequests) {}

    public function __invoke(ChargingRequest $chargingRequest): void
    {
        if ($chargingRequest->status->isTerminal()) {
            throw new LogicException('Cannot end a request that is already finished.');
        }

        $chargingRequest->markAs(ChargingRequestStatus::DONE);
        $this->chargingRequests->save($chargingRequest);
    }
}
