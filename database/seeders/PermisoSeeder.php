<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Los permisos representan vistas disponibles en el frontend Angular.
     * El nombre coincide con el nombre de la ruta/vista en el frontend.
     */
    public function run(): void
    {
        $permisos = [
            // ── Dashboard ─────────────────────────────────────────────
            ['perm_nombre' => 'dashboard'],

            // ── Cajas y movimientos ───────────────────────────────────
            ['perm_nombre' => 'cajas'],
            ['perm_nombre' => 'cajas.detalle'],
            ['perm_nombre' => 'movimientos'],
            ['perm_nombre' => 'movimientos.nuevo'],
            ['perm_nombre' => 'movimientos.excel'],
            ['perm_nombre' => 'movimientos.excel.plantilla'],
            ['perm_nombre' => 'movimientos.excel.exportar'],
            ['perm_nombre' => 'movimientos.excel.importar'],

            // ── Administración ────────────────────────────────────────
            ['perm_nombre' => 'usuarios'],
            ['perm_nombre' => 'usuarios.nuevo'],
            ['perm_nombre' => 'usuarios.editar'],
            ['perm_nombre' => 'usuarios.excel'],
            ['perm_nombre' => 'usuarios.excel.plantilla'],
            ['perm_nombre' => 'usuarios.excel.exportar'],
            ['perm_nombre' => 'usuarios.excel.importar'],
            ['perm_nombre' => 'locales'],
            ['perm_nombre' => 'locales.nuevo'],
            ['perm_nombre' => 'productos'],
            ['perm_nombre' => 'productos.nuevo'],
            ['perm_nombre' => 'roles'],
            ['perm_nombre' => 'reportes'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(['perm_nombre' => $permiso['perm_nombre']]);
        }
    }
}
