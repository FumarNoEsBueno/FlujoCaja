<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Perfil\UpdateCorreoRequest;
use App\Http\Requests\Perfil\UpdatePasswordRequest;
use App\Http\Resources\UsuarioResource;
use App\Http\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    use ApiResponser;

    /**
     * Retorna los datos del usuario autenticado.
     *
     * GET /api/perfil
     */
    public function show(): JsonResponse
    {
        try {
            /** @var \App\Models\Usuario $usuario */
            $usuario = Auth::guard('api')->user();

            return $this->successResponse(
                data: new UsuarioResource($usuario->load('rol')),
                message: 'Perfil obtenido correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener el perfil.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Actualiza el correo del usuario autenticado.
     *
     * PATCH /api/perfil/correo
     */
    public function updateCorreo(UpdateCorreoRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\Usuario $usuario */
            $usuario = Auth::guard('api')->user();

            $usuario->update(['usua_correo' => $request->usua_correo]);

            return $this->successResponse(
                data: new UsuarioResource($usuario->load('rol')),
                message: 'Correo actualizado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al actualizar el correo.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Cambia la contraseña del usuario autenticado.
     * Requiere verificación de la contraseña actual.
     *
     * PATCH /api/perfil/password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\Usuario $usuario */
            $usuario = Auth::guard('api')->user();

            if (! Hash::check($request->password_actual, $usuario->usua_password)) {
                return $this->errorResponse(
                    message: 'La contraseña actual es incorrecta.',
                    statusCode: 422,
                );
            }

            $usuario->update([
                'usua_password' => Hash::make($request->password_nuevo),
            ]);

            return $this->successResponse(
                message: 'Contraseña actualizada correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al actualizar la contraseña.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
