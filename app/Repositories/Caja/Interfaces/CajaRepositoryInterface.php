<?php

declare(strict_types=1);

namespace App\Repositories\Caja\Interfaces;

use App\Models\Caja;
use Illuminate\Database\Eloquent\Collection;

interface CajaRepositoryInterface
{
    /**
     * Retorna todas las cajas disponibles para el usuario autenticado.
     * Filtra por cajas habilitadas en usuarios_por_caja.
     *
     * @return Collection<int, Caja>
     */
    public function index(int $usuaId): Collection;

    /**
     * Retorna cajas para autocomplete: solo id y nombre, del usuario dado.
     *
     * @return Collection<int, Caja>
     */
    public function autocomplete(int $usuaId): Collection;

    /**
     * Retorna una caja por su ID.
     */
    public function show(int $id): Caja;
}
