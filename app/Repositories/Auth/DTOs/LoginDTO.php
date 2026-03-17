<?php

declare(strict_types=1);

namespace App\Repositories\Auth\DTOs;

use App\Http\Helpers\RutHelper;
use App\Http\Requests\Auth\LoginRequest;

final readonly class LoginDTO
{
    public function __construct(
        public int $rut,
        public string $dv,
        public string $password,
    ) {}

    /**
     * Construye el DTO directamente desde el FormRequest validado.
     */
    public static function fromRequest(LoginRequest $request): self
    {
        $rutFormated = (new RutHelper())->parse($request->rut);

        return new self(
            rut: $rutFormated['rut'],
            dv: $rutFormated['dv'], // normalizar DV a mayúscula
            password: $request->password,
        );
    }
}
