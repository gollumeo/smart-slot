<?php

declare(strict_types=1);

use App\Users\User;

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
        $existingUser = User::register('Pierre', 'pierre@izix.eu', 'mySoStrongPassword');
        $existingUser->save();

        $response = $this->postJson('/api/auth/token', [
            'email' => $existingUser->email,
            'password' => 'mySoStrongPassword',
            'device_name' => 'Postman',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    });
});
