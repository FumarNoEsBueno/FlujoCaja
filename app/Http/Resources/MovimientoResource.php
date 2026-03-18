<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $verificacion = $this->verificarMontos();

        return [
            'id'             => $this->id,
            'descripcion'    => $this->movi_descripcion,
            'fechaIngreso'   => $this->movi_fecha_ingreso?->format('d/m/Y'),
            'idTransaccion'  => $this->movi_id_transaccion,
            'montoTotal'     => $this->movi_monto_total,
            'medioPago'      => $this->movi_medio_pago,
            'propina'        => $this->movi_propina,
            'tipoMovimiento' => $this->tipoMovimiento?->timo_nombre,
            'usuario'        => $this->usuario
                ? trim($this->usuario->usua_nombre.' '.$this->usuario->usua_apellido_p)
                : null,
            'caja'           => $this->caja?->caja_nombre,
            'productos'      => $this->whenLoaded(
                'productosDelMovimiento',
                fn () => $this->productosDelMovimiento->map(fn ($p) => [
                    'id'            => $p->id,
                    'nombre'        => $p->producto?->prod_nombre,
                    'cantidad'      => $p->pdmo_cantidad,
                    'montoUnitario' => $p->pdmo_monto_unitario,
                ])->values(),
                [],
            ),
            'verificacion'   => $verificacion,
        ];
    }

    /**
     * Verifica si el monto declarado (total - propina) coincide con la suma de productos.
     *
     * Solo aplica cuando hay productos registrados. Si no los hay, no hay con qué
     * comparar — el monto total es responsabilidad del operador.
     *
     * @return array{aplica: bool, coincide: bool, montoDeclarado: string, montoProductos: string, motivos: string[]}
     */
    private function verificarMontos(): array
    {
        $productos = $this->relationLoaded('productosDelMovimiento')
            ? $this->productosDelMovimiento
            : collect();

        if ($productos->isEmpty()) {
            return [
                'aplica'         => false,
                'coincide'       => true,
                'montoDeclarado' => '0',
                'montoProductos' => '0',
                'motivos'        => [],
            ];
        }

        $montoTotal     = (float) $this->movi_monto_total;
        $propina        = (float) ($this->movi_propina ?? 0);

        // Lo que debería corresponder a los productos: total menos la propina del cliente
        $montoDeclarado = $montoTotal - $propina;

        // Suma real de los productos registrados
        $montoProductos = $productos->sum(
            fn ($p) => (float) $p->pdmo_monto_unitario * (int) $p->pdmo_cantidad
        );

        $coincide = abs($montoDeclarado - $montoProductos) < 0.01;

        return [
            'aplica'         => true,
            'coincide'       => $coincide,
            'montoDeclarado' => number_format($montoDeclarado, 2, '.', ''),
            'montoProductos' => number_format($montoProductos, 2, '.', ''),
            'motivos'        => $coincide ? [] : $this->detectarMotivos($montoDeclarado, $montoProductos, $propina, $montoTotal),
        ];
    }

    /**
     * Detecta posibles causas de la discrepancia.
     * Son orientativas — el operador puede tener razones válidas (descuentos, excepciones).
     *
     * @return string[]
     */
    private function detectarMotivos(
        float $montoDeclarado,
        float $montoProductos,
        float $propina,
        float $montoTotal,
    ): array {
        $motivos    = [];
        $diferencia = $montoDeclarado - $montoProductos;
        $productos  = $this->productosDelMovimiento;

        // ── Propina sospechosa ────────────────────────────────────────────────
        // Si sin restar la propina el total coincidiría, la propina está mal ingresada
        if ($propina > 0 && abs($montoTotal - $montoProductos) < 0.01) {
            $motivos[] = 'La propina parece estar mal ingresada: sin descontarla, el total coincide exactamente con los productos.';
        } elseif ($propina === 0.0 && abs($diferencia) > 0.01) {
            $motivos[] = 'No hay propina registrada. Si el cliente pagó propina, podría explicar la diferencia.';
        }

        // ── Cantidad incorrecta ───────────────────────────────────────────────
        // Si la diferencia es múltiplo exacto de algún precio unitario, falta o sobra esa cantidad
        foreach ($productos as $p) {
            $precio = (float) $p->pdmo_monto_unitario;
            if ($precio > 0 && abs(fmod(abs($diferencia), $precio)) < 0.01) {
                $unidades = (int) round(abs($diferencia) / $precio);
                $nombre   = $p->producto?->prod_nombre ?? 'un producto';
                $accion   = $diferencia > 0 ? 'de más' : 'de menos';
                $motivos[] = "Posible error de cantidad: {$unidades} unidad(es) {$accion} de \"{$nombre}\".";
                break;
            }
        }

        // ── Producto equivocado ───────────────────────────────────────────────
        if ($productos->count() > 1) {
            $motivos[] = 'Es posible que se haya seleccionado un producto equivocado en alguna línea.';
        }

        // ── Fallback ──────────────────────────────────────────────────────────
        if (empty($motivos)) {
            $motivos[] = 'La diferencia no responde a un patrón claro. Revisá los datos ingresados manualmente.';
        }

        return $motivos;
    }
}
