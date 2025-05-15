<?php

declare(strict_types=1);

namespace App\Users\Http\Controllers;

use App\Users\Http\Requests\Login;
use Illuminate\Http\JsonResponse;

final class AuthController
{
    public function __invoke(Login $request): JsonResponse
    {
        return new JsonResponse('hello-world', 200, [], JSON_PRETTY_PRINT);
    }
}
