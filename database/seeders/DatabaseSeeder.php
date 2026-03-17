<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Catálogos base (sin dependencias externas)
            RegionSeeder::class,          // → regiones
            ComunaSeeder::class,          // → comunas (necesita regiones)
            RolSeeder::class,             // → roles
            PermisoSeeder::class,         // → permisos

            // 2. Relaciones entre catálogos
            PermisosPorRolSeeder::class,  // → permisos_por_rol (necesita roles + permisos)

            // 3. Infraestructura de locales
            LocalSeeder::class,           // → locales (necesita comunas)
            CajaSeeder::class,            // → cajas (necesita locales)

            // 4. Productos (independiente)
            ProductoSeeder::class,        // → productos

            // 5. Usuarios y asignaciones (al final porque necesitan todo lo anterior)
            UsuarioSeeder::class,         // → usuarios (necesita roles)
            UsuariosPorCajaSeeder::class, // → usuarios_por_caja (necesita usuarios + cajas)
        ]);
    }
}
