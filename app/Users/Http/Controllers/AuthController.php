<?php

declare(strict_types=1);

namespace App\Users\Http\Controllers;

use App\Users\Http\Requests\Login;
use App\Users\Write\IssueAccessToken;
use Illuminate\Http\JsonResponse;

final class AuthController
{
    public function __construct(private readonly IssueAccessToken $issueToken) {}

    public function __invoke(Login $request): JsonResponse
    {
        /** @var array{email: string, password: string, device_name: string} $validated */
        $validated = $request->validated();

        $token = ($this->issueToken)(
            $validated['email'],
            $validated['password'],
            $validated['device_name']
        );

        return new JsonResponse(['token' => $token], 200, [], JSON_PRETTY_PRINT);
    }
}
