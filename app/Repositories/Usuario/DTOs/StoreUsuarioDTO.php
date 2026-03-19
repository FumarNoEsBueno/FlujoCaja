<?php

declare(strict_types=1);

namespace App\Repositories\Usuario\DTOs;

use App\Http\Requests\Usuario\StoreUsuarioRequest;

final readonly class StoreUsuarioDTO
{
    public function __construct(
        public string $usua_nombre,
        public string $usua_apellido_p,
        public ?string $usua_apellido_m,
        public string $usua_rut,
        public string $usua_dv,
        public ?string $usua_correo,
        public string $usua_fecha_nac,
        public string $usua_password,
        public int $role_id,
    ) {}

    public static function fromRequest(StoreUsuarioRequest $request): self
    {
        return new self(
            usua_nombre:     $request->usua_nombre,
            usua_apellido_p: $request->usua_apellido_p,
            usua_apellido_m: $request->usua_apellido_m,
            usua_rut:        $request->usua_rut,
            usua_dv:         $request->usua_dv,
            usua_correo:     $request->usua_correo,
            usua_fecha_nac:  $request->usua_fecha_nac,
            usua_password:   bcrypt($request->usua_password),
            role_id:         (int) $request->role_id,
        );
    }
}
