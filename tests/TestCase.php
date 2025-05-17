<?php

declare(strict_types=1);

namespace Tests;

use App\Users\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createTestUser(): User
    {
        $user = User::register('pierre', 'pierre@izix.eu', 'secret');
        $user->save();

        return $user;
    }
}
