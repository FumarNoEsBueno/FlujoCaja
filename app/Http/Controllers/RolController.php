<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Rol\StoreRolRequest;
use App\Http\Requests\Rol\UpdateRolRequest;
use App\Http\Resources\RolResource;
use App\Http\Traits\ApiResponser;
use App\Repositories\Rol\DTOs\StoreRolDTO;
use App\Repositories\Rol\DTOs\UpdateRolDTO;
use App\Repositories\Rol\Interfaces\RolRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly RolRepositoryInterface $rolRepository,
    ) {}

    /**
     * Lista roles paginados con filtros opcionales.
     *
     * GET /api/roles/table
     * Query params: nombre
     */
    public function table(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['nombre']);

            $paginator = $this->rolRepository->table($filters);

            $items = RolResource::collection($paginator->getCollection());

            return $this->paginatedResponse(
                data: $items,
                meta: [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'has_more' => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                ],
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

    /**
     * Crea un nuevo rol con sus permisos.
     *
     * POST /api/roles
     */
    public function store(StoreRolRequest $request): JsonResponse
    {
        try {
            $dto = StoreRolDTO::fromRequest($request);
            $rol = $this->rolRepository->store($dto);

            return $this->successResponse(
                data: new RolResource($rol),
                message: 'Rol creado correctamente.',
                statusCode: 201,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al crear el rol.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Retorna el detalle de un rol con sus permisos activos.
     *
     * GET /api/roles/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $rol = $this->rolRepository->show($id);
            $permisos = $this->rolRepository->permisos()
                ->map(fn ($p) => ['id' => $p->id, 'nombre' => $p->perm_nombre])
                ->values()
                ->toArray();

            $resource = (new RolResource($rol))
                ->additional(['todos_los_permisos' => $permisos]);

            return $this->successResponse(
                data: $resource,
                message: 'Rol obtenido correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Rol no encontrado.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Actualiza un rol y sincroniza sus permisos.
     *
     * PUT /api/roles/{id}
     */
    public function update(UpdateRolRequest $request, int $id): JsonResponse
    {
        try {
            $dto = UpdateRolDTO::fromRequest($request);
            $rol = $this->rolRepository->update($id, $dto);

            return $this->successResponse(
                data: new RolResource($rol),
                message: 'Rol actualizado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al actualizar el rol.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Elimina un rol.
     *
     * DELETE /api/roles/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->rolRepository->destroy($id);

            return $this->successResponse(
                message: 'Rol eliminado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al eliminar el rol.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Lista todos los roles para autocomplete.
     *
     * GET /api/roles/autocomplete
     * Retorna: [{ id, nombre }]
     */
    public function autocomplete(): JsonResponse
    {
        try {
            $roles = $this->rolRepository->table()
                ->getCollection()
                ->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->role_nombre])
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

    /**
     * Lista todos los permisos disponibles para asignar a un rol.
     *
     * GET /api/roles/permisos
     */
    public function permisos(): JsonResponse
    {
        try {
            $permisos = $this->rolRepository->permisos()
                ->map(fn ($p) => ['id' => $p->id, 'nombre' => $p->perm_nombre])
                ->values();

            return $this->successResponse(
                data: $permisos,
                message: 'Permisos obtenidos correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener los permisos.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
