<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El acceso al login siempre está permitido (sin auth previa)
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'rut' => ['required', 'string', 'max:10'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'usua_rut.required' => 'El RUT es obligatorio.',
            'usua_rut.max' => 'El RUT no puede superar los 10 caracteres.',
            'usua_dv.required' => 'El dígito verificador es obligatorio.',
            'usua_dv.max' => 'El dígito verificador debe ser un solo carácter.',
            'usua_password.required' => 'La contraseña es obligatoria.',
            'usua_password.min' => 'La contraseña debe tener al menos 6 caracteres.',
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
