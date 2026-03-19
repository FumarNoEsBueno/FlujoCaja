<?php

declare(strict_types=1);

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
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
            'usua_nombre'     => ['required', 'string', 'max:45'],
            'usua_apellido_p' => ['required', 'string', 'max:45'],
            'usua_apellido_m' => ['nullable', 'string', 'max:45'],
            'usua_rut'        => ['required', 'string', 'max:12'],
            'usua_dv'         => ['required', 'string', 'max:1'],
            'usua_correo'     => ['nullable', 'email', 'max:45', 'unique:usuarios,usua_correo'],
            'usua_fecha_nac'  => ['required', 'date_format:Y-m-d'],
            'usua_password'   => ['required', 'string', 'min:6'],
            'role_id'         => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'usua_nombre.required'     => 'El nombre es obligatorio.',
            'usua_apellido_p.required' => 'El apellido paterno es obligatorio.',
            'usua_rut.required'        => 'El RUT es obligatorio.',
            'usua_dv.required'         => 'El dígito verificador es obligatorio.',
            'usua_correo.email'        => 'El correo debe ser un email válido.',
            'usua_correo.unique'       => 'Este correo ya está registrado.',
            'usua_fecha_nac.required'  => 'La fecha de nacimiento es obligatoria.',
            'usua_fecha_nac.date_format' => 'La fecha debe tener el formato YYYY-MM-DD.',
            'usua_password.required'   => 'La contraseña es obligatoria.',
            'usua_password.min'        => 'La contraseña debe tener al menos 6 caracteres.',
            'role_id.required'         => 'El rol es obligatorio.',
            'role_id.exists'           => 'El rol seleccionado no existe.',
        ];
    }
}
