<?php

declare(strict_types=1);

namespace Database\Factories;

use App\ChargingSlots\ChargingSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChargingSlot>
 */
final class ChargingSlotFactory extends Factory
{
    protected $model = ChargingSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
