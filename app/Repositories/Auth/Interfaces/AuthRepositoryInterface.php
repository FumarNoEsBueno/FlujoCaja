<?php

declare(strict_types=1);

namespace App\Repositories\Auth\Interfaces;

use App\Repositories\Auth\DTOs\LoginDTO;

interface AuthRepositoryInterface
{
    /**
     * Autentica al usuario con las credenciales del DTO.
     * Retorna el token JWT o null si las credenciales son inválidas.
     */
    public function login(LoginDTO $dto): ?string;

    /**
     * Invalida el token actual del usuario autenticado.
     */
    public function logout(): void;

    /**
     * Refresca el token JWT actual y retorna uno nuevo.
     */
    public function refresh(): string;

    /**
     * Retorna los datos del usuario autenticado con su rol y permisos.
     *
     * @return array{usuario: array, permisos: string[]}
     */
    public function me(): array;
}
