<?php

declare(strict_types=1);

describe('Feature: Login Http Request', function () {
    it('ensures request validates required fields', function () {
        $response = test()->postJson('/api/auth/token');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    });

    it('ensures login request validates email format', function () {
        $response = $this->postJson('/api/auth/token', [
            'email' => 'not-an-email',
            'password' => 'my-lil-secret',
            'device_name' => 'Postman',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});
