<?php

declare(strict_types=1);

namespace App\ChargingRequests;

use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @property int $user_id
 * @property float $battery_percentage
 * @property CarbonInterface $starts_at
 * @property CarbonInterface $ends_at
 * @property ChargingRequestStatus $status
 * @property ?int $slot_id
 */
final class ChargingRequest extends Model
{
    public static function fromDomain(int $userId, BatteryPercentage $batteryPercentage, ChargingWindow $chargingWindow, ChargingRequestStatus $status = ChargingRequestStatus::QUEUED): self
    {
        $instance = new self();
        $instance->user_id = $userId;
        $instance->battery_percentage = $batteryPercentage->value();
        $instance->starts_at = $chargingWindow->start();
        $instance->ends_at = $chargingWindow->end();
        $instance->status = $status;

        return $instance;
    }

    public function markAs(ChargingRequestStatus $status): void
    {
        if ($status === ChargingRequestStatus::ASSIGNED && ! $this->slot_id) {
            throw new LogicException('Cannot assign a request without a slot.');
        }

        $this->status = $status;
    }
}
