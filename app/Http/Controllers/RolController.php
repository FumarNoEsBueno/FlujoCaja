<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use App\Models\Rol;
use Exception;
use Illuminate\Http\JsonResponse;

class RolController extends Controller
{
    use ApiResponser;

    /**
     * Lista todos los roles para autocomplete.
     *
     * GET /api/roles/autocomplete
     * Retorna: [{ id, nombre }]
     */
    public function autocomplete(): JsonResponse
    {
        try {
            $roles = Rol::orderBy('role_nombre')
                ->get(['id', 'role_nombre'])
                ->map(fn ($r) => [
                    'id'     => $r->id,
                    'nombre' => $r->role_nombre,
                ])
                ->values();

            return $this->successResponse(
                data: $roles,
                message: 'Roles obtenidos correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener los roles.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
