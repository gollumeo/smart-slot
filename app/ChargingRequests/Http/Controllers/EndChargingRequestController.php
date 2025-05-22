<?php

declare(strict_types=1);

namespace App\ChargingRequests\Http\Controllers;

use App\ChargingRequests\Http\Requests\UserEndsChargingRequest;
use App\ChargingRequests\Http\Resources\ChargingRequestResource;
use App\ChargingRequests\Write\EndChargingRequest;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use Illuminate\Http\JsonResponse;

final readonly class EndChargingRequestController
{
    public function __construct(private EndChargingRequest $endChargingRequest) {}

    /**
     * @throws CannotStartChargingRequest
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     */
    public function __invoke(UserEndsChargingRequest $request): JsonResponse
    {
        $chargingRequest = $request->chargingRequest();

        $this->endChargingRequest->execute($chargingRequest);

        return new ChargingRequestResource($chargingRequest)
            ->response()
            ->setStatusCode(200);
    }
}
