<?php

declare(strict_types=1);

namespace App\ChargingRequests\Write;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\Contracts\RepositoryContract;
use App\Users\User;

final readonly class StartChargingRequest
{
    public function __construct(
        private RepositoryContract $repository,
        private AssignSlotToRequest $assignSlot
    ) {}

    public function execute(User $user, ChargingWindow $chargingWindow, BatteryPercentage $batteryPercentage): ChargingRequest
    {
        $chargingRequest = new ChargingRequest([
            'user_id' => $user->id,
            'battery_percentage' => $batteryPercentage,
            'starts_at' => $chargingWindow->start(),
            'ends_at' => $chargingWindow->end(),
            'status' => ChargingRequestStatus::QUEUED,
        ]);

        $this->repository->save($chargingRequest);

        ($this->assignSlot)($chargingRequest);

        return $chargingRequest;
    }
}
