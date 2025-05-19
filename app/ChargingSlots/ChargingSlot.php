<?php

declare(strict_types=1);

namespace App\ChargingSlots;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $status
 * @property int $charging_request_id
 */
final class ChargingSlot extends Model {}
