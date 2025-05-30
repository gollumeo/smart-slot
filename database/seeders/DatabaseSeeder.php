<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\ChargingSlots\ChargingSlot;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ChargingSlot::factory(5)->create();
    }
}
