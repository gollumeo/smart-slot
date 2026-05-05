<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\ChargingSlots\ChargingSlot;
use App\Users\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ChargingSlot::factory(5)->create();

        User::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@test.com',
        ]);

        User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@test.com',
        ]);
    }
}
