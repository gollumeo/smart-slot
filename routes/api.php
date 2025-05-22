<?php

declare(strict_types=1);

use App\ChargingRequests\Http\Controllers\EndChargingRequestController;
use App\ChargingRequests\Http\Controllers\IntroduceChargingRequestController;
use App\Users\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', AuthController::class);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::prefix('/charging-requests')->name('charging-requests.')->group(function (): void {
        Route::post('', IntroduceChargingRequestController::class)->name('introduce');

        Route::post('{charging_request}/end', EndChargingRequestController::class)->name('end');
    });
});
