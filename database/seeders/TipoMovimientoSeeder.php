<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TipoMovimiento;
use Illuminate\Database\Seeder;

class TipoMovimientoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['timo_nombre' => 'Pago Transbank'],
        ];

        foreach ($tipos as $tipo) {
            TipoMovimiento::firstOrCreate(
                ['timo_nombre' => $tipo['timo_nombre']],
                $tipo,
            );
        }
    }
}
