<?php

declare(strict_types=1);

namespace App\Repositories\Rol\Eloquent;

use App\Models\Permiso;
use App\Models\Rol;
use App\Repositories\Rol\DTOs\StoreRolDTO;
use App\Repositories\Rol\DTOs\UpdateRolDTO;
use App\Repositories\Rol\Interfaces\RolRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class RolRepository implements RolRepositoryInterface
{
    private const PER_PAGE = 10;

    /**
     * @param  array{nombre?: string}  $filters
     */
    public function table(array $filters = []): Paginator
    {
        return Rol::when($filters['nombre'] ?? null, fn ($q, $v) => $q->where('role_nombre', 'LIKE', "%{$v}%"))
            ->with('permisosActivos')
            ->orderBy('role_nombre')
            ->simplePaginate(self::PER_PAGE);
    }

    public function show(int $id): Rol
    {
        /** @var Rol $rol */
        $rol = Rol::with('permisosActivos')->findOrFail($id);

        return $rol;
    }

    public function store(StoreRolDTO $dto): Rol
    {
        $rol = Rol::create(['role_nombre' => $dto->role_nombre]);

        $this->syncPermisos($rol, $dto->permIds);

        return $rol->load('permisosActivos');
    }

    public function update(int $id, UpdateRolDTO $dto): Rol
    {
        /** @var Rol $rol */
        $rol = Rol::findOrFail($id);

        $rol->update(['role_nombre' => $dto->role_nombre]);

        $this->syncPermisos($rol, $dto->permIds);

        return $rol->fresh('permisosActivos');
    }

    public function destroy(int $id): void
    {
        /** @var Rol $rol */
        $rol = Rol::findOrFail($id);

        // Eliminar todas las relaciones de permisos antes de borrar el rol
        $rol->permisosActivos()->detach();

        $rol->delete();
    }

    public function permisos(): Collection
    {
        return Permiso::orderBy('perm_nombre')->get();
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Sincroniza los permisos del rol usando la tabla pivot permisos_por_rol.
     * Los permisos recibidos se marcan como activos; los demás como inactivos.
     *
     * @param  int[]  $permIds
     */
    private function syncPermisos(Rol $rol, array $permIds): void
    {
        $todosLosPermisos = Permiso::pluck('id');

        $syncData = $todosLosPermisos->mapWithKeys(function (int $permId) use ($permIds): array {
            return [$permId => ['pero_activo' => in_array($permId, $permIds, strict: true)]];
        })->toArray();

        $rol->permisosActivos()->sync($syncData);
    }
}
