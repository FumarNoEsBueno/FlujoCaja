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
     *
     * Lógica:
     * - Si el permiso YA existe en la pivot → se SKIPEA (no se toca)
     * - Si el permiso NO existe → se CREA con pero_activo = true
     * - Si un permiso existente NO viene en $permIds → se DESHABILITA (pero_activo = false)
     *
     * Esto evita unique key errors por double-submit o condiciones de carrera.
     *
     * @param  int[]  $permIds
     */
    private function syncPermisos(Rol $rol, array $permIds): void
    {
        $permisosSet = collect($permIds);

        // 1) Deshabilitar permisos que ya existen pero NO vienen en $permIds
        $rol->permisosPorRol()
            ->whereNotIn('perm_id', $permisosSet)
            ->update(['pero_activo' => false]);

        // 2) Por cada permiso nuevo, usar firstOrCreate para evitar duplicates
        foreach ($permisosSet as $permId) {
            $rol->permisosPorRol()->firstOrCreate(
                ['perm_id' => $permId],
                ['pero_activo' => true],
            );
            // Si ya existía (por cualquier causa), asegurar que esté activo
            $rol->permisosPorRol()
                ->where('perm_id', $permId)
                ->update(['pero_activo' => true]);
        }
    }
}
