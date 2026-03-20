<?php

declare(strict_types=1);

namespace App\Repositories\Producto\Interfaces;

use App\Models\Producto;
use App\Repositories\Producto\DTOs\StoreProductoDTO;
use App\Repositories\Producto\DTOs\UpdateProductoDTO;
use Illuminate\Contracts\Pagination\Paginator;

interface ProductoRepositoryInterface
{
    /**
     * Lista productos paginados con filtros opcionales.
     *
     * @param  array{nombre?: string}  $filters
     */
    public function table(array $filters = []): Paginator;

    /**
     * Retorna un producto por su ID.
     */
    public function show(int $id): Producto;

    /**
     * Crea un nuevo producto y lo retorna.
     */
    public function store(StoreProductoDTO $dto): Producto;

    /**
     * Actualiza un producto existente y lo retorna.
     */
    public function update(int $id, UpdateProductoDTO $dto): Producto;

    /**
     * Elimina un producto por su ID.
     */
    public function destroy(int $id): void;
}
