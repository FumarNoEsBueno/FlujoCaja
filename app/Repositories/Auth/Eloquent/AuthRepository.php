<?php

declare(strict_types=1);

namespace App\Repositories\Auth\Eloquent;

use App\Models\Usuario;
use App\Repositories\Auth\DTOs\LoginDTO;
use App\Repositories\Auth\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthRepository implements AuthRepositoryInterface
{
    public function login(LoginDTO $dto): ?string
    {
        // 1. Buscar al usuario por RUT + DV (ambos campos en conjunto identifican al usuario)
        $usuario = Usuario::where('usua_rut', $dto->rut)
            ->where('usua_dv', $dto->dv)
            ->first();

        // 2. Verificar que el usuario existe y que la contraseña es correcta
        if (! $usuario || ! Hash::check($dto->password, $usuario->usua_password)) {
            return null;
        }

        // 3. Generar el token JWT para el usuario encontrado
        $token = Auth::guard('api')->login($usuario);

        return $token ?: null;
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    public function refresh(): string
    {
        try {
            return Auth::guard('api')->refresh();
        } catch (JWTException $e) {
            throw new RuntimeException('No se pudo refrescar el token.', 401, $e);
        }
    }

    public function me(): Usuario
    {
        /** @var Usuario */
        return Auth::guard('api')->user();
    }
}
