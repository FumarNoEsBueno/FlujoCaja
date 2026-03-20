<?php

declare(strict_types=1);

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
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
        $id = (int) $this->route('id');

        return [
            'prod_nombre' => ['required', 'string', 'max:100', Rule::unique('producto', 'prod_nombre')->ignore($id)],
            'prod_precio' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prod_nombre.required' => 'El nombre del producto es obligatorio.',
            'prod_nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'prod_nombre.unique' => 'Ya existe un producto con ese nombre.',
            'prod_precio.required' => 'El precio es obligatorio.',
            'prod_precio.numeric' => 'El precio debe ser un número.',
            'prod_precio.min' => 'El precio no puede ser negativo.',
        ];
    }
}
