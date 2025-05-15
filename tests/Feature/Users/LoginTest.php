<?php

declare(strict_types=1);

use App\Users\User;
use App\Users\Write\IssueAccessToken;

describe('Feature: Login', function (): void {
    it('ensures request validates required fields', function (): void {
        $response = test()->postJson('/api/auth/token');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    });

    it('ensures login request validates email format', function (): void {
        $response = $this->postJson('/api/auth/token', [
            'email' => 'not-an-email',
            'password' => 'my-lil-secret',
            'device_name' => 'Postman',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('ensures a registered user can log in', function (): void {
        $existingUser = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('mySoStrongPassword'),
        ]);
        $existingUser->save();

        $response = $this->postJson('/api/auth/token', [
            'email' => $existingUser->email,
            'password' => 'mySoStrongPassword',
            'device_name' => 'Postman',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    });

    it('denies access without proper authentication', function (): void {
        $this->getJson('/api/user')
            ->assertStatus(401);
    });

    it('returns the authenticated user making the request', function (): void {
        $user = new User([
            'name' => 'Pierre',
            'email' => 'pierre@izix.eu',
            'password' => Hash::make('mySoStrongPassword123.'),
        ]);

        $user->save();

        $token = new IssueAccessToken()(
            $user->email,
            'mySoStrongPassword123.',
            'Postman'
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/user');

        expect($response->status())->toBe(200)
            ->and($response->json('email'))->toBe($user->email);
    });
});
