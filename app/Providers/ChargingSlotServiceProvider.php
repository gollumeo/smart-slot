<?php

declare(strict_types=1);

namespace App\Providers;

use App\ChargingSlots\Infrastructure\Repositories\ChargingSlotsEloquent;
use App\ChargingSlots\Write\SlotAvailability;
use App\Contracts\ChargingRequestRepository;
use App\Contracts\ChargingSlotRepository;
use App\Contracts\SlotAvailabilityRules;
use Illuminate\Support\ServiceProvider;

final class ChargingSlotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChargingSlotRepository::class, ChargingSlotsEloquent::class);
        $this->app->bind(SlotAvailabilityRules::class, function ($app) {
            return new SlotAvailability(
                $app->make(ChargingRequestRepository::class)->getOngoingRequests()
            );
        });
    }

    public function boot(): void {}
}
