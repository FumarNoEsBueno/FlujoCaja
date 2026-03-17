<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_nombre' => 'Administrador'],
            ['role_nombre' => 'Trabajador'],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['role_nombre' => $rol['role_nombre']]);
        }
    }
}
