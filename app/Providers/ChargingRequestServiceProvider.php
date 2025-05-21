<?php

declare(strict_types=1);

namespace App\Providers;

use App\ChargingRequests\Infrastructure\Repositories\ChargingRequestsEloquent;
use App\Contracts\ChargingRequestRepository;
use Illuminate\Support\ServiceProvider;

final class ChargingRequestServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ChargingRequestRepository::class, ChargingRequestsEloquent::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
