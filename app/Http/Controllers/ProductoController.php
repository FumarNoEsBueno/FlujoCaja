<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;

class ProductoController extends Controller
{
    use ApiResponser;

    /**
     * Lista simplificada de productos para autocomplete.
     *
     * GET /api/productos/autocomplete
     */
    public function autocomplete(): JsonResponse
    {
        $productos = Producto::select(['id', 'prod_nombre', 'prod_precio'])
            ->orderBy('prod_nombre')
            ->get()
            ->map(fn (Producto $p) => [
                'id'     => $p->id,
                'label'  => $p->prod_nombre,
                'precio' => $p->prod_precio,
            ]);

        return $this->successResponse(
            data: $productos,
            message: 'Productos obtenidos correctamente.',
        );
    }
}
