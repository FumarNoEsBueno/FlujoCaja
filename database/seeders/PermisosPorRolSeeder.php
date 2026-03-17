<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\PermisosPorRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class PermisosPorRolSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Rol::where('role_nombre', 'Administrador')->first();
        $trabajador = Rol::where('role_nombre', 'Trabajador')->first();
        $permisos = Permiso::all()->keyBy('perm_nombre');

        // ── Administrador: acceso total ────────────────────────────────
        $permisosAdmin = [
            'dashboard',
            'cajas', 'cajas.detalle',
            'movimientos', 'movimientos.nuevo',
            'usuarios', 'usuarios.nuevo', 'usuarios.editar',
            'locales', 'locales.nuevo',
            'productos', 'productos.nuevo',
            'roles',
            'reportes',
        ];

        foreach ($permisosAdmin as $nombre) {
            $permiso = $permisos->get($nombre);
            if (! $permiso) {
                continue;
            }

            PermisosPorRol::firstOrCreate(
                ['perm_id' => $permiso->id, 'role_id' => $admin->id],
                ['pero_activo' => true],
            );
        }

        // ── Trabajador: solo operación de caja ────────────────────────
        $permisosTrabajador = [
            'dashboard',
            'cajas', 'cajas.detalle',
            'movimientos', 'movimientos.nuevo',
        ];

        foreach ($permisosTrabajador as $nombre) {
            $permiso = $permisos->get($nombre);
            if (! $permiso) {
                continue;
            }

            PermisosPorRol::firstOrCreate(
                ['perm_id' => $permiso->id, 'role_id' => $trabajador->id],
                ['pero_activo' => true],
            );
        }

        // Permisos que existen pero el trabajador NO tiene → los registramos desactivados
        $permisosRestantes = $permisos->keys()->diff($permisosTrabajador);

        foreach ($permisosRestantes as $nombre) {
            $permiso = $permisos->get($nombre);
            if (! $permiso) {
                continue;
            }

            PermisosPorRol::firstOrCreate(
                ['perm_id' => $permiso->id, 'role_id' => $trabajador->id],
                ['pero_activo' => false],
            );
        }
    }
}
