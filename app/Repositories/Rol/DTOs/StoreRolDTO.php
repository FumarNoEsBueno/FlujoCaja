<?php

declare(strict_types=1);

namespace App\Repositories\Rol\DTOs;

use App\Http\Requests\Rol\StoreRolRequest;

final readonly class StoreRolDTO
{
    /**
     * @param  int[]  $permIds  IDs de permisos a asignar al rol
     */
    public function __construct(
        public string $role_nombre,
        public array $permIds,
    ) {}

    public static function fromRequest(StoreRolRequest $request): self
    {
        return new self(
            role_nombre: $request->role_nombre,
            permIds: $request->perm_ids ?? [],
        );
    }
}
