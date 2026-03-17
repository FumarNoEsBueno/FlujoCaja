<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // ── Bebestibles ───────────────────────────────────────────
            ['prod_nombre' => 'Café Americano',        'prod_precio' => 1500],
            ['prod_nombre' => 'Café con Leche',        'prod_precio' => 1800],
            ['prod_nombre' => 'Cappuccino',            'prod_precio' => 2200],
            ['prod_nombre' => 'Té',                    'prod_precio' => 1200],
            ['prod_nombre' => 'Bebida en Lata',        'prod_precio' => 1500],
            ['prod_nombre' => 'Agua Mineral',          'prod_precio' => 1000],
            ['prod_nombre' => 'Jugo Natural',          'prod_precio' => 2000],
            ['prod_nombre' => 'Milo',                  'prod_precio' => 1600],

            // ── Comida salada ─────────────────────────────────────────
            ['prod_nombre' => 'Completo Italiano',     'prod_precio' => 2500],
            ['prod_nombre' => 'Completo con Palta',    'prod_precio' => 2800],
            ['prod_nombre' => 'Sandwich Lomito',       'prod_precio' => 4500],
            ['prod_nombre' => 'Sandwich Mechada',      'prod_precio' => 3800],
            ['prod_nombre' => 'Sandwich Ave Palta',    'prod_precio' => 3500],
            ['prod_nombre' => 'Churrasco',             'prod_precio' => 4200],
            ['prod_nombre' => 'Empanada de Pino',      'prod_precio' => 1800],
            ['prod_nombre' => 'Empanada de Queso',     'prod_precio' => 1600],
            ['prod_nombre' => 'Sopaipilla',            'prod_precio' => 500],

            // ── Dulces y repostería ───────────────────────────────────
            ['prod_nombre' => 'Brownie',               'prod_precio' => 1800],
            ['prod_nombre' => 'Queque de Chocolate',   'prod_precio' => 1600],
            ['prod_nombre' => 'Facturas',              'prod_precio' => 700],
            ['prod_nombre' => 'Croissant',             'prod_precio' => 1400],
            ['prod_nombre' => 'Berlín',                'prod_precio' => 900],
            ['prod_nombre' => 'Pie de Limón',          'prod_precio' => 2200],
            ['prod_nombre' => 'Cheesecake',            'prod_precio' => 2500],
            ['prod_nombre' => 'Alfajor',               'prod_precio' => 1000],
        ];

        foreach ($productos as $producto) {
            Producto::firstOrCreate(
                ['prod_nombre' => $producto['prod_nombre']],
                ['prod_precio' => $producto['prod_precio']],
            );
        }
    }
}
