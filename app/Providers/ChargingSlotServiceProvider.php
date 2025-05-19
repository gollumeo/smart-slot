<?php

declare(strict_types=1);

namespace App\Providers;

use App\ChargingSlots\Infrastructure\Repositories\ChargingSlotsEloquent;
use App\Contracts\ChargingSlotRepository;
use Illuminate\Support\ServiceProvider;

final class ChargingSlotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChargingSlotRepository::class, ChargingSlotsEloquent::class);
    }

    public function boot(): void {}
}
