<?php

declare(strict_types=1);

namespace App\ChargingRequests\Http\Resources;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ChargingRequest
 */
final class ChargingRequestResource extends JsonResource
{
    /**
     * @return array{id: int, slot_id: int|null, status: ChargingRequestStatus}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slot_id' => $this->slot_id,
            'status' => $this->status,
        ];
    }
}
