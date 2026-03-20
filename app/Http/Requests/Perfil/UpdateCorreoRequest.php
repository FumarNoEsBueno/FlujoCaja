<?php

declare(strict_types=1);

namespace App\Http\Requests\Perfil;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCorreoRequest extends FormRequest
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
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::guard('api')->user();

        return [
            'usua_correo' => ['required', 'email', 'max:150', "unique:usuarios,usua_correo,{$usuario->getKey()}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'usua_correo.required' => 'El correo es obligatorio.',
            'usua_correo.email'    => 'El correo debe ser un email válido.',
            'usua_correo.max'      => 'El correo no puede superar los 150 caracteres.',
            'usua_correo.unique'   => 'Este correo ya está registrado.',
        ];
    }
}
