<?php

declare(strict_types=1);

namespace App\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class Login extends FormRequest
{
    /**
     * @return array<string, string[]>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
