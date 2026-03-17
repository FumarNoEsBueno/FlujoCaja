<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regiones = [
            ['regi_nombre' => 'Región de Arica y Parinacota'],
            ['regi_nombre' => 'Región de Tarapacá'],
            ['regi_nombre' => 'Región de Antofagasta'],
            ['regi_nombre' => 'Región de Atacama'],
            ['regi_nombre' => 'Región de Coquimbo'],
            ['regi_nombre' => 'Región de Valparaíso'],
            ['regi_nombre' => 'Región Metropolitana de Santiago'],
            ['regi_nombre' => "Región del Libertador General Bernardo O'Higgins"],
            ['regi_nombre' => 'Región del Maule'],
            ['regi_nombre' => 'Región de Ñuble'],
            ['regi_nombre' => 'Región del Biobío'],
            ['regi_nombre' => 'Región de La Araucanía'],
            ['regi_nombre' => 'Región de Los Ríos'],
            ['regi_nombre' => 'Región de Los Lagos'],
            ['regi_nombre' => 'Región de Aysén del General Carlos Ibáñez del Campo'],
            ['regi_nombre' => 'Región de Magallanes y de la Antártica Chilena'],
        ];

        foreach ($regiones as $region) {
            Region::firstOrCreate(['regi_nombre' => $region['regi_nombre']]);
        }
    }
}
