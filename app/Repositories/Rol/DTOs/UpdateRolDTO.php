<?php

declare(strict_types=1);

namespace App\Repositories\Rol\DTOs;

use App\Http\Requests\Rol\UpdateRolRequest;

final readonly class UpdateRolDTO
{
    /**
     * @param  int[]  $permIds  IDs de permisos a sincronizar en el rol
     */
    public function __construct(
        public string $role_nombre,
        public array $permIds,
    ) {}

    public static function fromRequest(UpdateRolRequest $request): self
    {
        return new self(
            role_nombre: $request->role_nombre,
            permIds: $request->perm_ids ?? [],
        );
    }
}
