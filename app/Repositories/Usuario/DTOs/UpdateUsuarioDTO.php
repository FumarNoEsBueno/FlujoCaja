<?php

declare(strict_types=1);

namespace App\Repositories\Usuario\DTOs;

use App\Http\Helpers\RutHelper;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;

final readonly class UpdateUsuarioDTO
{
    public function __construct(
        public ?string  $usua_nombre,
        public ?string  $usua_apellido_p,
        public ?string  $usua_apellido_m,
        public ?int     $usua_rut,
        public ?string  $usua_dv,
        public ?string  $usua_correo,
        public ?string  $usua_fecha_nac,
        public ?string  $usua_password,
        public ?int     $role_id,
        /** @var array<string, bool> Claves que estuvieron presentes en el request (para distinguir null intencional) */
        public array    $present,
    ) {}

    public static function fromRequest(UpdateUsuarioRequest $request): self
    {
        $parsed = null;

        if ($request->filled('usua_rut')) {
            // Si el frontend envía rut y dv por separado, recombinar para parsear.
            $rutCompleto = $request->filled('usua_dv')
                ? $request->usua_rut . '-' . $request->usua_dv
                : $request->usua_rut;

            $parsed = RutHelper::parse($rutCompleto);
        }

        return new self(
            usua_nombre:     $request->usua_nombre,
            usua_apellido_p: $request->usua_apellido_p,
            usua_apellido_m: $request->usua_apellido_m,
            usua_rut:        $parsed['rut'] ?? null,
            usua_dv:         $parsed['dv'] ?? null,
            usua_correo:     $request->usua_correo,
            usua_fecha_nac:  $request->usua_fecha_nac,
            usua_password:   $request->filled('usua_password') ? bcrypt($request->usua_password) : null,
            role_id:         $request->role_id !== null ? (int) $request->role_id : null,
            present:         array_fill_keys($request->keys(), true),
        );
    }
}
