<?php

declare(strict_types=1);

namespace App\Repositories\Movimiento\Interfaces;

use App\Models\Movimiento;
use App\Repositories\Movimiento\DTOs\StoreMovimientoDTO;
use App\Repositories\Movimiento\DTOs\UpdateMovimientoDTO;
use Illuminate\Pagination\AbstractPaginator;

interface MovimientoRepositoryInterface
{
    /**
     * Lista movimientos paginados (10 por página) con filtros opcionales.
     *
     * @param  array{movi_id_transaccion?: string, caja_id?: int, usua_id?: int, movi_fecha_ingreso?: string}  $filters
     */
    public function table(array $filters = []): AbstractPaginator;

    /**
     * Crea un nuevo movimiento y lo retorna con relaciones cargadas.
     */
    public function store(StoreMovimientoDTO $dto): Movimiento;

    /**
     * Retorna un movimiento por su ID con todas sus relaciones.
     */
    public function show(int $id): Movimiento;

    /**
     * Actualiza un movimiento existente y lo retorna con relaciones cargadas.
     */
    public function update(int $id, UpdateMovimientoDTO $dto): Movimiento;

    /**
     * Elimina un movimiento por su ID.
     */
    public function destroy(int $id): void;
}
