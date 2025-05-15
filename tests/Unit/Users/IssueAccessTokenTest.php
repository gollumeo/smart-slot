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
});
