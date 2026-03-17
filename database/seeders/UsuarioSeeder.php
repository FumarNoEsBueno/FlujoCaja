<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Rol::where('role_nombre', 'Administrador')->first();
        $trabajador = Rol::where('role_nombre', 'Trabajador')->first();

        $usuarios = [
            // ── Administradores ───────────────────────────────────────
            [
                'usua_nombre' => 'Manuel',
                'usua_apellido_p' => 'Pereira',
                'usua_apellido_m' => 'Opazo',
                'usua_rut' => '20194802',
                'usua_dv' => '9',
                'usua_correo' => 'mpereira9909@gmail.com',
                'usua_fecha_nac' => '1999-06-24',
                'usua_password' => Hash::make('123456'),
                'role_id' => $admin->id,
            ],
            [
                'usua_nombre' => 'Marcelo',
                'usua_apellido_p' => 'Murillo',
                'usua_apellido_m' => null,
                'usua_rut' => '20254526',
                'usua_dv' => '2',
                'usua_correo' => 'marcelo.murillo.99@hotmail.com',
                'usua_fecha_nac' => '1999-05-31',
                'usua_password' => Hash::make('123456'),
                'role_id' => $admin->id,
            ],

            // ── Trabajador ────────────────────────────────────────────
            [
                'usua_nombre' => 'Jean',
                'usua_apellido_p' => 'Germain',
                'usua_apellido_m' => 'Anguita',
                'usua_rut' => '20023269',
                'usua_dv' => '0',
                'usua_correo' => 'va.orialco@gmail.com',
                'usua_fecha_nac' => '1999-04-15',
                'usua_password' => Hash::make('123456'),
                'role_id' => $trabajador->id,
            ],
        ];

        foreach ($usuarios as $data) {
            Usuario::firstOrCreate(
                ['usua_correo' => $data['usua_correo']],
                $data,
            );
        }
    }
}
