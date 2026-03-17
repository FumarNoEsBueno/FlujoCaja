<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Local;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        // Cada local tiene entre 2 y 3 cajas registradoras
        $cajasPorLocal = [
            'Local Providencia' => ['Caja 1', 'Caja 2'],
            'Local Las Condes' => ['Caja 1', 'Caja 2', 'Caja 3'],
            'Local Santiago Centro' => ['Caja 1', 'Caja 2', 'Caja 3'],
            'Local Ñuñoa' => ['Caja 1', 'Caja 2'],
            'Local Viña del Mar' => ['Caja 1', 'Caja 2'],
        ];

        foreach ($cajasPorLocal as $localNombre => $cajas) {
            $local = Local::where('loca_nombre', $localNombre)->first();

            if (! $local) {
                continue;
            }

            foreach ($cajas as $cajaNombre) {
                Caja::firstOrCreate(
                    ['caja_nombre' => $cajaNombre, 'loca_id' => $local->id],
                );
            }
        }
    }
}
