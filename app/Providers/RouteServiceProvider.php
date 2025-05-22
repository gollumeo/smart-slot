<?php

declare(strict_types=1);

namespace App\Providers;

use App\ChargingRequests\ChargingRequest;
use Illuminate\Support\ServiceProvider;
use Route;

final class RouteServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::model('charging_request', ChargingRequest::class);
    }
}
