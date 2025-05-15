<?php

declare(strict_types=1);

namespace App\Users\Write;

use App\Users\User;
use Hash;
use Illuminate\Validation\ValidationException;

final class IssueAccessToken
{
    public function __invoke(string $email, string $password, string $device): ?string
    {
        $user = $this->retrieveUserBy($email);

        $this->verifyCredentials($user, $password);

        $this->purgeExistingTokens($user, $device);

        return $user->createToken($device)->plainTextToken;
    }

    private function retrieveUserBy(string $email): User
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        return $user;
    }

    private function verifyCredentials(User $user, string $password): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }
    }

    private function purgeExistingTokens(User $user, string $device): void
    {
        $user->tokens()->where('name', $device)->delete();
    }
}
