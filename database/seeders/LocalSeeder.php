<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comuna;
use App\Models\Local;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    public function run(): void
    {
        // Usamos comunas de la RM y Valparaíso donde una cadena de cafetería tendría locales
        $locales = [
            [
                'loca_nombre' => 'Local Providencia',
                'loca_direccion' => 'Av. Providencia 1250',
                'comu_nombre' => 'Providencia',
            ],
            [
                'loca_nombre' => 'Local Las Condes',
                'loca_direccion' => 'Av. Apoquindo 4500',
                'comu_nombre' => 'Las Condes',
            ],
            [
                'loca_nombre' => 'Local Santiago Centro',
                'loca_direccion' => 'Paseo Ahumada 340',
                'comu_nombre' => 'Santiago',
            ],
            [
                'loca_nombre' => 'Local Ñuñoa',
                'loca_direccion' => 'Av. Irarrázaval 3200',
                'comu_nombre' => 'Ñuñoa',
            ],
            [
                'loca_nombre' => 'Local Viña del Mar',
                'loca_direccion' => 'Av. Libertad 1020',
                'comu_nombre' => 'Viña del Mar',
            ],
        ];

        foreach ($locales as $data) {
            $comuna = Comuna::where('comu_nombre', $data['comu_nombre'])->first();

            if (! $comuna) {
                continue;
            }

            Local::firstOrCreate(
                ['loca_nombre' => $data['loca_nombre']],
                [
                    'loca_direccion' => $data['loca_direccion'],
                    'comu_id' => $comuna->id,
                ],
            );
        }
    }
}
