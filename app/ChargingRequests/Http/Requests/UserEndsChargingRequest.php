<?php

declare(strict_types=1);

namespace App\ChargingRequests\Http\Requests;

use App\ChargingRequests\ChargingRequest;
use Illuminate\Foundation\Http\FormRequest;

final class UserEndsChargingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->id === $this->chargingRequest()->user_id;
    }

    public function chargingRequest(): ChargingRequest
    {
        /** @var ChargingRequest $chargingRequest */
        $chargingRequest = $this->route('charging_request');

        return $chargingRequest;
    }

    public function rules(): array
    {
        return [];
    }
}
