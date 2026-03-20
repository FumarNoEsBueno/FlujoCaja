<?php

declare(strict_types=1);

namespace App\Http\Requests\Rol;

use Illuminate\Foundation\Http\FormRequest;

class StoreRolRequest extends FormRequest
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
            'role_nombre' => ['required', 'string', 'max:45', 'unique:roles,role_nombre'],
            'perm_ids' => ['nullable', 'array'],
            'perm_ids.*' => ['integer', 'exists:permisos,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role_nombre.required' => 'El nombre del rol es obligatorio.',
            'role_nombre.max' => 'El nombre no puede superar los 45 caracteres.',
            'role_nombre.unique' => 'Ya existe un rol con ese nombre.',
            'perm_ids.array' => 'Los permisos deben ser un arreglo de IDs.',
            'perm_ids.*.integer' => 'Cada permiso debe ser un ID numérico.',
            'perm_ids.*.exists' => 'Uno o más permisos seleccionados no son válidos.',
        ];
    }
}
