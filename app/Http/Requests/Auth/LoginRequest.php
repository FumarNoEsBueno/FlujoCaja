<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\RutValidator;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'rut' => ['required', 'string', 'max:12', new RutValidator()],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rut.required' => 'El RUT es obligatorio.',
            'rut.max' => 'El RUT no puede superar los 12 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ];
    }

    /**
     * Retorna las credenciales limpias para el repositorio.
     *
     * @return array{usua_rut: string, usua_dv: string, usua_password: string}
     */
    public function credentials(): array
    {
        return $this->only(['usua_rut', 'usua_dv', 'usua_password']);
    }
}
