<?php

declare(strict_types=1);

namespace App\Http\Requests\Movimiento;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovimientoRequest extends FormRequest
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
            'movi_descripcion' => ['required', 'string', 'max:255'],
            'movi_fecha_ingreso' => ['required', 'date_format:Y-m-d'],
            'movi_monto_total' => ['required', 'string', 'max:45'],
            'movi_medio_pago' => ['required', 'string', 'max:100'],
            'movi_propina' => ['nullable', 'string', 'max:45'],
            'caja_id' => ['required', 'integer', 'exists:cajas,id'],
            // Productos (opcional)
            'productos' => ['nullable', 'array'],
            'productos.*.prod_id' => ['required_with:productos', 'integer', 'exists:producto,id'],
            'productos.*.pdmo_cantidad' => ['required_with:productos', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'movi_descripcion.required' => 'La descripción es obligatoria.',
            'movi_fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'movi_fecha_ingreso.date_format' => 'La fecha debe tener el formato YYYY-MM-DD.',
            'movi_monto_total.required' => 'El monto total es obligatorio.',
            'movi_medio_pago.required' => 'El medio de pago es obligatorio.',
            'caja_id.required' => 'Debe seleccionar una caja.',
            'caja_id.exists' => 'La caja seleccionada no existe.',
            'productos.*.prod_id.exists' => 'Uno o más productos seleccionados no existen.',
            'productos.*.pdmo_cantidad.min' => 'La cantidad de cada producto debe ser al menos 1.',
        ];
    }
}
