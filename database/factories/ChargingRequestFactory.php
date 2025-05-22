<?php

declare(strict_types=1);

namespace Database\Factories;

use App\ChargingRequests\ChargingRequest;
use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Random\RandomException;

/**
 * @extends Factory<ChargingRequest>
 */
final class ChargingRequestFactory extends Factory
{
    protected $model = ChargingRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws RandomException
     */
    public function create($attributes = [], ?Model $parent = null): ChargingRequest
    {
        /** @var int $userId */
        $userId = $attributes['user_id'] ?? User::factory()->create()->id;
        /** @var float $battery */
        $battery = $attributes['battery_percentage'] ?? random_int(10, 80);

        $start = now()->addMinutes(random_int(5, 30))->toImmutable();
        $end = $start->addHour();

        $window = new ChargingWindow($start, $end);

        /** @var ChargingRequestStatus $status */
        $status = $attributes['status'] ?? ChargingRequestStatus::QUEUED;

        $model = ChargingRequest::fromDomain(
            $userId,
            new BatteryPercentage($battery),
            $window,
            $status
        );

        if (isset($attributes['slot_id'])) {
            $model->slot_id = $attributes['slot_id'];
        }

        $model->save();

        return $model;
    }
}
