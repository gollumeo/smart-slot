<?php

declare(strict_types=1);

namespace App\ChargingRequests\Http\Controllers;

use App\ChargingRequests\Http\Requests\IntroduceChargingRequest;
use App\ChargingRequests\Http\Resources\ChargingRequestResource;
use App\ChargingRequests\Write\StartChargingRequest;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use Illuminate\Http\JsonResponse;

final readonly class IntroduceChargingRequestController
{
    public function __construct(private StartChargingRequest $startChargingRequest) {}

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     * @throws CannotStartChargingRequest
     * @throws UserAlreadyHasActiveChargingRequest
     */
    public function __invoke(IntroduceChargingRequest $request): JsonResponse
    {
        $chargingRequest = $this->startChargingRequest->execute(
            user: $request->chargingRequestUser(),
            chargingWindow: $request->chargingWindow(),
            batteryPercentage: $request->batteryPercentage(),
        );

        return new ChargingRequestResource($chargingRequest)
            ->response()
            ->setStatusCode(201);
    }
}
