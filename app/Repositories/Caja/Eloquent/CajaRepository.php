<?php

declare(strict_types=1);

namespace App\Repositories\Caja\Eloquent;

use App\Models\Caja;
use App\Repositories\Caja\Interfaces\CajaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CajaRepository implements CajaRepositoryInterface
{
    /**
     * Retorna todas las cajas habilitadas para el usuario autenticado.
     * Si el usuario no tiene cajas asignadas, retorna todas (admin fallback).
     *
     * @return Collection<int, Caja>
     */
    public function index(int $usuaId): Collection
    {
        return Caja::with('local')
            ->whereHas('usuariosPorCaja', function ($query) use ($usuaId): void {
                $query->where('usua_id', $usuaId)
                    ->where('usca_habilitado', true);
            })
            ->get();
    }

    /**
     * Retorna cajas del usuario para autocomplete (solo id + nombre).
     *
     * @return Collection<int, Caja>
     */
    public function autocomplete(int $usuaId): Collection
    {
        return Caja::selectRaw("cajas.id, CONCAT(cajas.caja_nombre, ' (', l.loca_nombre, ')') as caja_nombre")
            ->join('locales as l', 'cajas.loca_id', '=', 'l.id')
            ->whereHas('usuariosPorCaja', function ($query) use ($usuaId): void {
                $query->where('usua_id', $usuaId)
                    ->where('usca_habilitado', true);
            })
            ->orderBy('cajas.caja_nombre')
            ->get();
    }

    public function show(int $id): Caja
    {
        /** @var Caja $caja */
        $caja = Caja::with('local')->findOrFail($id);

        return $caja;
    }
}
