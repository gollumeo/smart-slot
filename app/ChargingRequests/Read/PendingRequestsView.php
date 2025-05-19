<?php

declare(strict_types=1);

namespace App\ChargingRequests\Read;

use App\Contracts\ChargingRequestRepository;
use Illuminate\Support\Collection;

final class PendingRequestsView
{
    public function __construct(private ChargingRequestRepository $repository) {}

    public function get(): Collection
    {
        return $this->repository->getPendingRequests();
    }
}
