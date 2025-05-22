<?php

declare(strict_types=1);

namespace App\ChargingRequests;

use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\ChargingSlots\ChargingSlot;
use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use Carbon\CarbonImmutable;
use Database\Factories\ChargingRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    /** @use HasFactory<ChargingRequestFactory> */
    use HasFactory;

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
     * @throws CannotStartChargingRequest
     * @throws CannotAssignRequestWithoutSlot
     * @throws ChargingRequestAlreadyFinished
     */
    public function assignTo(ChargingSlot $slot): void
    {
        $this->slot_id = $slot->id;
        $this->markAs(ChargingRequestStatus::ASSIGNED);
    }

    /**
     * @throws CannotAssignRequestWithoutSlot
     * @throws CannotStartChargingRequest
     * @throws ChargingRequestAlreadyFinished
     */
    public function markAs(ChargingRequestStatus $status): void
    {
        if ($this->status->isTerminal()) {
            throw new ChargingRequestAlreadyFinished();
        }

        if ($status === ChargingRequestStatus::ASSIGNED && ! $this->slot_id) {
            throw new CannotAssignRequestWithoutSlot();
        }

        if ($status === ChargingRequestStatus::CHARGING && $this->status !== ChargingRequestStatus::ASSIGNED) {
            throw new CannotStartChargingRequest();
        }

        $this->status = $status;
    }

    public function conflictsWith(ChargingWindow $window): bool
    {
        return $this->starts_at < $window->end() && $this->ends_at > $window->start();
    }

    protected static function newFactory(): ChargingRequestFactory
    {
        return new ChargingRequestFactory();
    }
}
