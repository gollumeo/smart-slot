<?php

declare(strict_types=1);

namespace App\ChargingRequests;

use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use Illuminate\Database\Eloquent\Model;

final class ChargingRequest extends Model
{
    public static function fromDomain(int $userId, BatteryPercentage $batteryPercentage, ChargingWindow $chargingWindow, ChargingRequestStatus $status): self
    {
        $instance = new self();
        $instance->user_id = $userId;
        $instance->battery_percentage = $batteryPercentage->value();
        $instance->starts_at = $chargingWindow->start();
        $instance->ends_at = $chargingWindow->end();
        $instance->status = $status;

        return $instance;
    }
}
