<?php

declare(strict_types=1);

namespace App\Users\Write;

use App\Users\User;
use Hash;
use Illuminate\Validation\ValidationException;

final class IssueAccessToken
{
    public function __invoke(string $email, string $password, string $device): string
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $user->tokens()->where('name', $device)->delete();

        return $user->createToken($device)->plainTextToken;
    }
}
