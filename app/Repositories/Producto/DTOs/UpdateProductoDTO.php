<?php

declare(strict_types=1);

namespace App\Repositories\Producto\DTOs;

use App\Http\Requests\Producto\UpdateProductoRequest;

final readonly class UpdateProductoDTO
{
    public function __construct(
        public string $prod_nombre,
        public float $prod_precio,
    ) {}

    public static function fromRequest(UpdateProductoRequest $request): self
    {
        return new self(
            prod_nombre: $request->prod_nombre,
            prod_precio: (float) $request->prod_precio,
        );
    }
}
