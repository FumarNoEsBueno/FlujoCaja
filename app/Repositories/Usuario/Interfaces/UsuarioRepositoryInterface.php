<?php

declare(strict_types=1);

namespace App\Repositories\Usuario\Interfaces;

use App\Models\Usuario;
use App\Models\UsuariosPorCaja;
use App\Repositories\Usuario\DTOs\StoreUsuarioDTO;
use App\Repositories\Usuario\DTOs\UpdateUsuarioDTO;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface UsuarioRepositoryInterface
{
    /**
     * Lista usuarios paginados con filtros opcionales.
     *
     * @param  array{nombre?: string, rut?: string, role_id?: int}  $filters
     */
    public function table(array $filters = []): Paginator;

    /**
     * Retorna un usuario con sus relaciones (rol + cajas).
     */
    public function show(int $id): Usuario;

    /**
     * Crea un nuevo usuario y lo retorna con el rol cargado.
     */
    public function store(StoreUsuarioDTO $dto): Usuario;

    /**
     * Actualiza un usuario existente y lo retorna con el rol cargado.
     */
    public function update(int $id, UpdateUsuarioDTO $dto): Usuario;

    /**
     * Elimina un usuario por su ID.
     */
    public function destroy(int $id): void;

    // ─── Cajas ────────────────────────────────────────────────────────────────

    /**
     * Retorna todas las cajas asignadas a un usuario.
     */
    public function getCajas(int $usuaId): Collection;

    /**
     * Asigna una caja a un usuario.
     */
    public function asignarCaja(int $usuaId, int $cajaId, bool $habilitado, ?string $fechaInicio): UsuariosPorCaja;

    /**
     * Desvincula una caja de un usuario.
     */
    public function quitarCaja(int $usuaId, int $cajaId): void;

    /**
     * Activa/desactiva la caja asignada a un usuario.
     */
    public function toggleCaja(int $usuaId, int $cajaId): UsuariosPorCaja;
}
