<?php

declare(strict_types=1);

namespace App\Repositories\Movimiento\Eloquent;

use App\Models\Movimiento;
use App\Models\ProductosDelMovimiento;
use App\Repositories\Movimiento\DTOs\StoreMovimientoDTO;
use App\Repositories\Movimiento\DTOs\UpdateMovimientoDTO;
use App\Repositories\Movimiento\Interfaces\MovimientoRepositoryInterface;
use Illuminate\Pagination\AbstractPaginator;

class MovimientoRepository implements MovimientoRepositoryInterface
{
    private const PER_PAGE = 10;

    /**
     * @param  array{movi_id_transaccion?: string, caja_id?: int, usua_id?: int, movi_fecha_ingreso?: string}  $filters
     */
    public function table(array $filters = []): AbstractPaginator
    {
        $query = Movimiento::with(['tipoMovimiento', 'usuario', 'caja'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['movi_id_transaccion'])) {
            $query->where('movi_id_transaccion', $filters['movi_id_transaccion']);
        }

        if (! empty($filters['caja_id'])) {
            $query->where('caja_id', (int) $filters['caja_id']);
        }

        if (! empty($filters['usua_id'])) {
            $query->where('usua_id', (int) $filters['usua_id']);
        }

        if (! empty($filters['movi_fecha_ingreso'])) {
            $query->whereDate('movi_fecha_ingreso', $filters['movi_fecha_ingreso']);
        }

        return $query->simplePaginate(self::PER_PAGE);
    }

    public function store(StoreMovimientoDTO $dto): Movimiento
    {
        $movimiento = Movimiento::create([
            'movi_descripcion' => $dto->movi_descripcion,
            'movi_fecha_ingreso' => $dto->movi_fecha_ingreso,
            'movi_id_transaccion' => $dto->movi_id_transaccion,
            'movi_monto_total' => $dto->movi_monto_total,
            'movi_medio_pago' => $dto->movi_medio_pago,
            'movi_propina' => $dto->movi_propina,
            'timo_id' => $dto->timo_id,
            'usua_id' => $dto->usua_id,
            'caja_id' => $dto->caja_id,
        ]);

        if (! empty($dto->productos)) {
            $rows = array_map(
                fn (array $p) => [
                    'movi_id' => $movimiento->id,
                    'prod_id' => $p['prod_id'],
                    'pdmo_cantidad' => $p['pdmo_cantidad'],
                    'pdmo_monto_unitario' => $p['pdmo_monto_unitario'],
                ],
                $dto->productos,
            );

            ProductosDelMovimiento::insert($rows);
        }

        $movimiento->load(['tipoMovimiento', 'usuario', 'caja']);

        return $movimiento;
    }

    public function show(int $id): Movimiento
    {
        /** @var Movimiento $movimiento */
        $movimiento = Movimiento::select(
            'id',
            'movi_fecha_ingreso',
            'movi_descripcion',
            'movi_id_transaccion',
            'movi_monto_total',
            'movi_medio_pago',
            'movi_propina',
            'timo_id',
            'usua_id',
            'caja_id',
        )
            ->with([
                'tipoMovimiento',
                'usuario',
                'caja',
                'productosDelMovimiento.producto',
            ])
            ->findOrFail($id);

        return $movimiento;
    }

    public function update(int $id, UpdateMovimientoDTO $dto): Movimiento
    {
        $movimiento = Movimiento::findOrFail($id);

        $movimiento->update([
            'movi_descripcion' => $dto->movi_descripcion,
            'movi_fecha_ingreso' => $dto->movi_fecha_ingreso,
            'movi_monto_total' => $dto->movi_monto_total,
            'movi_medio_pago' => $dto->movi_medio_pago,
            'movi_propina' => $dto->movi_propina,
        ]);

        // Eliminar productos existentes y re-insertar los nuevos
        ProductosDelMovimiento::where('movi_id', $movimiento->id)->delete();

        if (! empty($dto->productos)) {
            $rows = array_map(
                fn (array $p) => [
                    'movi_id' => $movimiento->id,
                    'prod_id' => $p['prod_id'],
                    'pdmo_cantidad' => $p['pdmo_cantidad'],
                    'pdmo_monto_unitario' => $p['pdmo_monto_unitario'],
                ],
                $dto->productos,
            );

            ProductosDelMovimiento::insert($rows);
        }

        $movimiento->load(['tipoMovimiento', 'usuario', 'caja']);

        return $movimiento;
    }

    public function destroy(int $id): void
    {
        Movimiento::findOrFail($id)->delete();
    }
}
