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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
            'device_name.required' => 'Device name is required.',
            'email.email' => 'Email is not valid.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
