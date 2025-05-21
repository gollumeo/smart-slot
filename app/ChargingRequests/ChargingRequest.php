<?php

declare(strict_types=1);

namespace App\ChargingRequests;

use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property float $battery_percentage
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property ChargingRequestStatus $status
 * @property ?int $slot_id
 * @property ?CarbonImmutable $charging_started_at
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

    public function chargingWindow(): ChargingWindow
    {
        return new ChargingWindow($this->starts_at, $this->ends_at);
    }

    /**
     * @throws CannotAssignRequestWithoutSlot
     */
    public function assignTo(ChargingSlot $slot): void
    {
        $this->slot_id = $slot->id;
        $this->markAs(ChargingRequestStatus::ASSIGNED);
    }

    /**
     * @throws CannotAssignRequestWithoutSlot
     */
    public function markAs(ChargingRequestStatus $status): void
    {
        if ($status === ChargingRequestStatus::ASSIGNED && ! $this->slot_id) {
            throw new CannotAssignRequestWithoutSlot();
        }

        $this->status = $status;
    }

    public function conflictsWith(ChargingWindow $window): bool
    {
        return $this->starts_at < $window->end() && $this->ends_at > $window->start();
    }
}
