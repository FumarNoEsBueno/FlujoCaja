<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CajaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'nombre' => $this->caja_nombre,
            'local'  => $this->whenLoaded('local', fn () => [
                'id'        => $this->local->id,
                'nombre'    => $this->local->loca_nombre,
                'direccion' => $this->local->loca_direccion,
            ]),
        ];
    }
}
