<?php

declare(strict_types=1);

namespace App\Contracts;

use App\ChargingRequests\ChargingRequest;
use App\Users\User;
use Illuminate\Support\Collection;

interface ChargingRequestRepository
{
    public function save(ChargingRequest $chargingRequest): void;

    public function hasActiveRequestFor(User $user): bool;

    public function getPendingRequests(): Collection;

    public function getActiveRequestFor(User $user): ?ChargingRequest;
}
