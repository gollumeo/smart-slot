<?php

declare(strict_types=1);

use App\Users\User;
use App\Users\Write\IssueAccessToken;
use Illuminate\Validation\ValidationException;

describe('Unit: Issue Access Token', function (): void {

    it('returns a token with valid credentials', function (): void {
        $user = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('secret'),
        ]);
        $user->save();

        $token = new IssueAccessToken()($user->email, 'secret', 'Postman');

        expect($token)->toBeString()->not()->toBeEmpty();
    });

    it('throws when email is unknown', function (): void {

        expect(fn () => new IssueAccessToken()('not-an-email-address', 'secret', 'Postman'))
            ->toThrow(ValidationException::class);
    });

    it('throws with wrong password', function (): void {
        $user = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('secret'),
        ]);
        $user->save();

        expect(fn () => new IssueAccessToken()($user->email, 'pwd', 'Postman'))
            ->toThrow(ValidationException::class);
    });

    it('throws if user is not found', function (): void {
        expect(fn () => new IssueAccessToken()('not-an-email-address', 'secret', 'Postman'))
            ->toThrow(ValidationException::class);
    });

    it('returns only the token string', function () {
        $user = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('secret'),
        ]);
        $user->save();

        $token = new IssueAccessToken()($user->email, 'secret', 'Postman');
        expect($token)->toBeString()
            ->not()->toBeEmpty()
            ->not()->toStartWith('{')
            ->not()->toContain('token')
            ->not()->toContain('user');
    });

    it('deletes old token for the same device before issuing a new one', function (): void {
        $user = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('another-pwd'),
        ]);
        $user->save();

        new IssueAccessToken()($user->email, 'another-pwd', 'Postman');

        // not using Pest.php's toBeOne macro because of PHPStan
        expect($user->tokens()->count())->toBe(1);
    });
});
