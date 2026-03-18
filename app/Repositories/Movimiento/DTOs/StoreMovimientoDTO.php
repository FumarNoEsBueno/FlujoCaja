<?php

declare(strict_types=1);

namespace App\Repositories\Movimiento\DTOs;

use App\Http\Requests\Movimiento\StoreMovimientoRequest;
use Illuminate\Support\Str;

final readonly class StoreMovimientoDTO
{
    /**
     * @param  array<int, array{prod_id: int, pdmo_cantidad: int, pdmo_monto_unitario: string}>  $productos
     */
    public function __construct(
        public string $movi_descripcion,
        public string $movi_fecha_ingreso,
        public string $movi_id_transaccion,
        public string $movi_monto_total,
        public string $movi_medio_pago,
        public ?string $movi_propina,
        public int $caja_id,
        public int $usua_id,
        public int $timo_id,
        public array $productos = [],
    ) {}

    public static function fromRequest(StoreMovimientoRequest $request, int $usuaId, int $timoId): self
    {
        // Enriquecer productos con monto_unitario desde la BD
        $productosRaw = $request->productos ?? [];
        $productos    = [];

        if (! empty($productosRaw)) {
            $ids      = collect($productosRaw)->pluck('prod_id')->unique()->toArray();
            $precios  = \App\Models\Producto::whereIn('id', $ids)
                ->pluck('prod_precio', 'id');

            foreach ($productosRaw as $item) {
                $productos[] = [
                    'prod_id'            => (int) $item['prod_id'],
                    'pdmo_cantidad'      => (int) $item['pdmo_cantidad'],
                    'pdmo_monto_unitario' => (string) ($precios[$item['prod_id']] ?? '0'),
                ];
            }
        }

        return new self(
            movi_descripcion:    $request->movi_descripcion,
            movi_fecha_ingreso:  $request->movi_fecha_ingreso,
            movi_id_transaccion: (string) Str::uuid(),
            movi_monto_total:    $request->movi_monto_total,
            movi_medio_pago:     $request->movi_medio_pago,
            movi_propina:        $request->movi_propina,
            caja_id:             (int) $request->caja_id,
            usua_id:             $usuaId,
            timo_id:             $timoId,
            productos:           $productos,
        );
    }
}
