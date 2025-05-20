<?php

declare(strict_types=1);

namespace Tests;

use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\Policies\ChargingRequestEligibility;
use App\Users\User;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    final public function createStaticTestUser(): User
    {
        $user = User::register('pierre', 'pierre@izix.eu', 'secret');
        $user->save();

        return $user;
    }

    /**
     * @throws Exception
     */
    final public function createWindow(string $startTime, string $endTime): ChargingWindow
    {
        $startTime = $this->parseCarbon($startTime);
        $endTime = $this->parseCarbon($endTime);

        return new ChargingWindow($startTime, $endTime);
    }

    /**
     * @throws Exception
     */
    final public function parseCarbon(string $dateTime): CarbonImmutable
    {
        $carbon = CarbonImmutable::createFromFormat('d-m-Y H:i', $dateTime);

        if (! $carbon instanceof CarbonImmutable) {
            throw new Exception('Carbon date must be a valid date.');
        }

        return $carbon;
    }

    final public function makeMock(string $className): MockInterface
    {
        return Mockery::mock($className);
    }

    final public function mockEligibility(callable $expectations): ChargingRequestEligibility
    {
        $mock = Mockery::mock(ChargingRequestEligibility::class);
        $expectations($mock);

        return $mock;
    }
}
