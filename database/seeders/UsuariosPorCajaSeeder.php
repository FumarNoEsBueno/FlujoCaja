<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Usuario;
use App\Models\UsuariosPorCaja;
use Illuminate\Database\Seeder;

class UsuariosPorCajaSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::all();
        $cajas = Caja::all();

        // Todos los usuarios tienen acceso a todas las cajas, habilitados desde hoy
        foreach ($usuarios as $usuario) {
            foreach ($cajas as $caja) {
                UsuariosPorCaja::firstOrCreate(
                    [
                        'usua_id' => $usuario->id,
                        'caja_id' => $caja->id,
                    ],
                    [
                        'usca_habilitado' => true,
                        'usca_fecha_inicio' => now()->toDateString(),
                    ],
                );
            }
        }
    }
}
