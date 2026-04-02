<?php

declare(strict_types=1);

namespace App\Http\Requests\Perfil;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password_actual' => ['required', 'string'],
            'password_nuevo' => ['required', 'string', 'min:8', 'confirmed'],
            'password_nuevo_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password_actual.required' => 'La contraseña actual es obligatoria.',
            'password_nuevo.required' => 'La nueva contraseña es obligatoria.',
            'password_nuevo.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password_nuevo.confirmed' => 'La confirmación de contraseña no coincide.',
            'password_nuevo_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
        ];
    }
}
