<?php

declare(strict_types=1);

namespace App\Repositories\Usuario\Eloquent;

use App\Models\Usuario;
use App\Models\UsuariosPorCaja;
use App\Repositories\Usuario\DTOs\StoreUsuarioDTO;
use App\Repositories\Usuario\DTOs\UpdateUsuarioDTO;
use App\Repositories\Usuario\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    private const PER_PAGE = 10;

    /**
     * @param  array{nombre?: string, rut?: string, role_id?: int}  $filters
     */
    public function table(array $filters = []): Paginator
    {
        return Usuario::with('rol')
            ->withCount('usuariosPorCaja')
            ->when($filters['nombre'] ?? null, fn ($q, $v) =>
                $q->where(
                    DB::raw("CONCAT(usua_nombre, ' ', usua_apellido_p)"),
                    'LIKE',
                    "%{$v}%"
                )
            )
            ->when($filters['rut'] ?? null, fn ($q, $v) =>
                $q->where('usua_rut', 'LIKE', "%{$v}%")
            )
            ->when($filters['role_id'] ?? null, fn ($q, $v) =>
                $q->where('role_id', $v)
            )
            ->orderBy('usua_nombre')
            ->simplePaginate(self::PER_PAGE);
    }

    public function show(int $id): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Usuario::with(['rol', 'usuariosPorCaja.caja.local'])
            ->findOrFail($id);

        return $usuario;
    }

    public function store(StoreUsuarioDTO $dto): Usuario
    {
        $usuario = Usuario::create([
            'usua_nombre'     => $dto->usua_nombre,
            'usua_apellido_p' => $dto->usua_apellido_p,
            'usua_apellido_m' => $dto->usua_apellido_m,
            'usua_rut'        => $dto->usua_rut,
            'usua_dv'         => $dto->usua_dv,
            'usua_correo'     => $dto->usua_correo,
            'usua_fecha_nac'  => $dto->usua_fecha_nac,
            'usua_password'   => $dto->usua_password,
            'role_id'         => $dto->role_id,
        ]);

        return $usuario->load('rol');
    }

    public function update(int $id, UpdateUsuarioDTO $dto): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Usuario::findOrFail($id);

        // Sólo incluir en el payload los campos que vinieron en el request.
        // Se usa $dto->present para distinguir "no enviado" de "enviado como null".
        $fields = [
            'usua_nombre',
            'usua_apellido_p',
            'usua_apellido_m',
            'usua_rut',
            'usua_dv',
            'usua_correo',
            'usua_fecha_nac',
            'role_id',
        ];

        $payload = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $dto->present)) {
                $payload[$field] = $dto->{$field};
            }
        }

        // La contraseña solo se actualiza si se envió y no es null
        if (array_key_exists('usua_password', $dto->present) && $dto->usua_password !== null) {
            $payload['usua_password'] = $dto->usua_password;
        }

        if (! empty($payload)) {
            $usuario->update($payload);
        }

        return $usuario->load('rol');
    }

    public function destroy(int $id): void
    {
        Usuario::findOrFail($id)->delete();
    }

    // ─── Cajas ────────────────────────────────────────────────────────────────

    public function getCajas(int $usuaId): Collection
    {
        return UsuariosPorCaja::with('caja.local')
            ->where('usua_id', $usuaId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function asignarCaja(int $usuaId, int $cajaId, bool $habilitado = true, ?string $fechaInicio = null): UsuariosPorCaja
    {
        /** @var UsuariosPorCaja $uc */
        $uc = UsuariosPorCaja::create([
            'usua_id'           => $usuaId,
            'caja_id'           => $cajaId,
            'usca_habilitado'   => $habilitado,
            'usca_fecha_inicio' => $fechaInicio ?? now()->toDateString(),
        ]);

        return $uc->load('caja.local');
    }

    public function quitarCaja(int $usuaId, int $cajaId): void
    {
        UsuariosPorCaja::where('usua_id', $usuaId)
            ->where('caja_id', $cajaId)
            ->firstOrFail()
            ->delete();
    }

    public function toggleCaja(int $usuaId, int $cajaId): UsuariosPorCaja
    {
        /** @var UsuariosPorCaja $uc */
        $uc = UsuariosPorCaja::where('usua_id', $usuaId)
            ->where('caja_id', $cajaId)
            ->firstOrFail();

        $uc->update(['usca_habilitado' => ! $uc->usca_habilitado]);

        return $uc->fresh()->load('caja.local');
    }
}
