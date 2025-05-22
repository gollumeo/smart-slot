<?php

declare(strict_types=1);

namespace App\ChargingRequests\Http\Requests;

use App\ChargingRequests\ValueObjects\BatteryPercentage;
use App\ChargingRequests\ValueObjects\ChargingWindow;
use App\Users\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

final class IntroduceChargingRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'battery_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'charging_window.start_time' => ['required', 'date', 'date_format:d-m-Y H:i'],
            'charging_window.end_time' => ['required', 'date', 'date_format:d-m-Y H:i', 'after:charging_window.start_time'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function chargingWindow(): ChargingWindow
    {
        /** @var string $start */
        $start = $this->input('charging_window.start_time');
        /** @var string $end */
        $end = $this->input('charging_window.end_time');

        return new ChargingWindow(
            CarbonImmutable::parse($start),
            CarbonImmutable::parse($end)
        );
    }

    public function batteryPercentage(): BatteryPercentage
    {
        /** @var float|int|string $value */
        $value = $this->input('battery_percentage');

        return new BatteryPercentage((float) $value);
    }

    public function chargingRequestUser(): User
    {
        /** @var User $user */
        $user = parent::user();

        return $user;
    }
}
