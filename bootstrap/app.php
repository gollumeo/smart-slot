<?php

declare(strict_types=1);

use App\Exceptions\CannotAssignRequestWithoutSlot;
use App\Exceptions\CannotStartChargingRequest;
use App\Exceptions\ChargingRequestAlreadyFinished;
use App\Exceptions\UserAlreadyHasActiveChargingRequest;
use App\Http\Middleware\ForceJson;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(ForceJson::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (
            UserAlreadyHasActiveChargingRequest|
            ChargingRequestAlreadyFinished|
            CannotAssignRequestWithoutSlot|
            CannotStartChargingRequest $e
        ) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => class_basename($e),
            ], 422, [], JSON_PRETTY_PRINT);
        });
    })->create();
