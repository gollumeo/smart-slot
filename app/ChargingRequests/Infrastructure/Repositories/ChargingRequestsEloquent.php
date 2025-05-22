<?php

declare(strict_types=1);

namespace App\ChargingRequests\Infrastructure\Repositories;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\Contracts\ChargingRequestRepository;
use App\Users\User;
use Illuminate\Support\Collection;

final class ChargingRequestsEloquent implements ChargingRequestRepository
{
    public function save(ChargingRequest $chargingRequest): void
    {
        // TODO: Implement save() method.
    }

    public function hasActiveRequestFor(User $user): bool
    {
        return $this->getActiveRequestFor($user) !== null;
    }

    public function getActiveRequestFor(User $user): ?ChargingRequest
    {
        return ChargingRequest::where('user_id', $user->id)
            ->whereIn('status', [
                ChargingRequestStatus::QUEUED,
                ChargingRequestStatus::ASSIGNED,
                ChargingRequestStatus::CHARGING,
            ])
            ->orderByDesc('created_at')
            ->first();
    }

    public function getPendingRequests(): Collection
    {
        return ChargingRequest::where('status', ChargingRequestStatus::QUEUED)
            ->orderBy('starts_at')
            ->orderBy('battery_percentage')
            ->get();
    }

    public function getOngoingRequests(): Collection
    {
        return ChargingRequest::whereIn('status', [
            ChargingRequestStatus::QUEUED,
            ChargingRequestStatus::ASSIGNED,
            ChargingRequestStatus::CHARGING,
        ])->get();
    }
}
