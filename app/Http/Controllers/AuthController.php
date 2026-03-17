<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\ApiResponser;
use App\Repositories\Auth\DTOs\LoginDTO;
use App\Repositories\Auth\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
    ) {}

    /**
     * Autentica al usuario y retorna un token JWT.
     *
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $token = $this->authRepository->login(LoginDTO::fromRequest($request));

            if (! $token) {
                return $this->errorResponse('Credenciales inválidas.', 401);
            }

            return $this->successResponse(
                data: $this->buildTokenPayload($token),
                message: 'Sesión iniciada correctamente.',
                statusCode: 200,
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                message: 'Error al intentar iniciar sesión.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Retorna el usuario autenticado actualmente.
     *
     * GET /api/auth/me
     */
    public function me(): JsonResponse
    {
        try {
            $usuario = $this->authRepository->me();

            return $this->successResponse(
                data: $usuario,
                message: 'Usuario autenticado.',
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                message: 'No se pudo obtener el usuario.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Refresca el token JWT del usuario actual.
     *
     * POST /api/auth/refresh
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = $this->authRepository->refresh();

            return $this->successResponse(
                data: $this->buildTokenPayload($token),
                message: 'Token renovado correctamente.',
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                message: 'No se pudo renovar el token.',
                statusCode: 401,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Cierra la sesión del usuario invalidando el token.
     *
     * POST /api/auth/logout
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authRepository->logout();

            return $this->successResponse(
                message: 'Sesión cerrada correctamente.',
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                message: 'Error al cerrar sesión.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Construye el payload estándar de respuesta con el token.
     *
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    private function buildTokenPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }
}
