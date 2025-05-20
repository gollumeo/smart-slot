<?php

declare(strict_types=1);

namespace App\ChargingRequests\Read;

use App\ChargingRequests\ChargingRequest;
use App\Contracts\ChargingRequestRepository;
use Illuminate\Support\Collection;

final readonly class PendingRequestsView
{
    public function __construct(private ChargingRequestRepository $repository) {}

    /**
     * @return Collection<int, ChargingRequest>
     */
    public function get(): Collection
    {
        return $this->repository->getPendingRequests();
    }
}
