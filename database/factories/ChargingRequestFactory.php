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

    private ?ChargingWindow $customWindow = null;

    public function withWindow(ChargingWindow $window): self
    {
        $clone = clone $this;
        $clone->customWindow = $window;

        return $clone;
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
        $battery = ($attributes['battery_percentage'] ?? random_int(10, 80));

        $window = $this->customWindow ?? new ChargingWindow(
            now()->addMinutes(random_int(5, 30))->toImmutable(),
            now()->addMinutes(random_int(65, 90))->toImmutable()
        );

        /** @var ChargingRequestStatus $status */
        $status = $attributes['status'] ?? ChargingRequestStatus::QUEUED;

        $chargingRequest = ChargingRequest::fromDomain(
            $userId,
            new BatteryPercentage($battery),
            $window,
            $status
        );

        if (array_key_exists('slot_id', $attributes)) {
            /** @var int|null $slotId */
            $slotId = $attributes['slot_id'];
            $chargingRequest->slot_id = $slotId;
        }

        $chargingRequest->save();

        return $chargingRequest;
    }

    public function definition()
    {
        //
    }
}
