<?php

declare(strict_types=1);

namespace App\Repositories\Rol\Interfaces;

use App\Models\Permiso;
use App\Models\Rol;
use App\Repositories\Rol\DTOs\StoreRolDTO;
use App\Repositories\Rol\DTOs\UpdateRolDTO;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface RolRepositoryInterface
{
    /**
     * Lista roles paginados con filtros opcionales.
     *
     * @param  array{nombre?: string}  $filters
     */
    public function table(array $filters = []): Paginator;

    /**
     * Retorna un rol por su ID, incluyendo sus permisos activos.
     */
    public function show(int $id): Rol;

    /**
     * Crea un nuevo rol con sus permisos y lo retorna.
     */
    public function store(StoreRolDTO $dto): Rol;

    /**
     * Actualiza un rol existente y sincroniza sus permisos.
     */
    public function update(int $id, UpdateRolDTO $dto): Rol;

    /**
     * Elimina un rol por su ID.
     */
    public function destroy(int $id): void;

    /**
     * Lista todos los permisos disponibles (sin paginación).
     *
     * @return Collection<int, Permiso>
     */
    public function permisos(): Collection;
}
