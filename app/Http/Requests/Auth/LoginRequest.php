<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El campo correo es obligatorio.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'email.email' => 'El campo correo debe ser una dirección de correo electrónico válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'correo',
            'password' => 'contraseña',
        ];
    }
}
