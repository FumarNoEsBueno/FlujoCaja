<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->role_nombre,
            'permisos' => $this->whenLoaded('permisosActivos', fn () => $this->permisosActivos
                ->map(fn ($p) => ['id' => $p->id, 'nombre' => $p->perm_nombre])
                ->values()
                ->toArray()
            ),
            'todos_los_permisos' => $this->when(
                isset($this->additional['todos_los_permisos']),
                fn () => $this->additional['todos_los_permisos'],
            ),
        ];
    }
}
