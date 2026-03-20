<?php

declare(strict_types=1);

namespace App\Repositories\Producto\Eloquent;

use App\Models\Producto;
use App\Repositories\Producto\DTOs\StoreProductoDTO;
use App\Repositories\Producto\DTOs\UpdateProductoDTO;
use App\Repositories\Producto\Interfaces\ProductoRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class ProductoRepository implements ProductoRepositoryInterface
{
    private const PER_PAGE = 10;

    /**
     * @param  array{nombre?: string}  $filters
     */
    public function table(array $filters = []): Paginator
    {
        return Producto::when($filters['nombre'] ?? null, fn ($q, $v) => $q->where('prod_nombre', 'LIKE', "%{$v}%")
        )
            ->orderBy('prod_nombre')
            ->simplePaginate(self::PER_PAGE);
    }

    public function show(int $id): Producto
    {
        /** @var Producto $producto */
        $producto = Producto::findOrFail($id);

        return $producto;
    }

    public function store(StoreProductoDTO $dto): Producto
    {
        return Producto::create([
            'prod_nombre' => $dto->prod_nombre,
            'prod_precio' => $dto->prod_precio,
        ]);
    }

    public function update(int $id, UpdateProductoDTO $dto): Producto
    {
        /** @var Producto $producto */
        $producto = Producto::findOrFail($id);

        $producto->update([
            'prod_nombre' => $dto->prod_nombre,
            'prod_precio' => $dto->prod_precio,
        ]);

        return $producto->fresh();
    }

    public function destroy(int $id): void
    {
        Producto::findOrFail($id)->delete();
    }
}
