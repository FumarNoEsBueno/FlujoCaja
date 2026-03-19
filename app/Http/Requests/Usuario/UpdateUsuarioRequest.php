<?php

declare(strict_types=1);

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
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
        $usuarioId = (int) $this->route('id');

        return [
            'usua_nombre'     => ['sometimes', 'string', 'max:45'],
            'usua_apellido_p' => ['sometimes', 'string', 'max:45'],
            'usua_apellido_m' => ['sometimes', 'nullable', 'string', 'max:45'],
            'usua_rut'        => ['sometimes', 'string', 'max:12'],
            'usua_dv'         => ['sometimes', 'string', 'max:1'],
            'usua_correo'     => ['sometimes', 'nullable', 'email', 'max:45', "unique:usuarios,usua_correo,{$usuarioId}"],
            'usua_fecha_nac'  => ['sometimes', 'date_format:Y-m-d'],
            'usua_password'   => ['sometimes', 'nullable', 'string', 'min:6'],
            'role_id'         => ['sometimes', 'integer', 'exists:roles,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'usua_correo.email'          => 'El correo debe ser un email válido.',
            'usua_correo.unique'         => 'Este correo ya está registrado.',
            'usua_fecha_nac.date_format' => 'La fecha debe tener el formato YYYY-MM-DD.',
            'usua_password.min'          => 'La contraseña debe tener al menos 6 caracteres.',
            'role_id.exists'             => 'El rol seleccionado no existe.',
        ];
    }
}
