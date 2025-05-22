<?php

declare(strict_types=1);

namespace App\ChargingSlots;

use Database\Factories\ChargingSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $status
 */
final class ChargingSlot extends Model
{
    /** @use HasFactory<ChargingSlotFactory> */
    use HasFactory;

    protected static function newFactory(): ChargingSlotFactory
    {
        return ChargingSlotFactory::new();
    }
}
