<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Http\Traits\ApiResponser;
use App\Repositories\Usuario\DTOs\StoreUsuarioDTO;
use App\Repositories\Usuario\DTOs\UpdateUsuarioDTO;
use App\Repositories\Usuario\Interfaces\UsuarioRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * Lista usuarios paginados con filtros opcionales.
     *
     * GET /api/usuarios/table
     * Query params: nombre, rut, role_id
     */
    public function table(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['nombre', 'rut', 'role_id']);

            $paginator = $this->usuarioRepository->table($filters);

            $items = UsuarioResource::collection($paginator->getCollection());

            return $this->paginatedResponse(
                data: $items,
                meta: [
                    'current_page'  => $paginator->currentPage(),
                    'per_page'      => $paginator->perPage(),
                    'has_more'      => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                ],
                message: 'Usuarios obtenidos correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener los usuarios.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Crea un nuevo usuario.
     *
     * POST /api/usuarios
     */
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        try {
            $dto     = StoreUsuarioDTO::fromRequest($request);
            $usuario = $this->usuarioRepository->store($dto);

            return $this->successResponse(
                data: new UsuarioResource($usuario),
                message: 'Usuario creado correctamente.',
                statusCode: 201,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al crear el usuario.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Retorna el detalle de un usuario (con cajas asignadas).
     *
     * GET /api/usuarios/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $usuario = $this->usuarioRepository->show($id);

            return $this->successResponse(
                data: new UsuarioResource($usuario),
                message: 'Usuario obtenido correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Usuario no encontrado.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Actualiza un usuario existente.
     *
     * PUT /api/usuarios/{id}
     */
    public function update(UpdateUsuarioRequest $request, int $id): JsonResponse
    {
        try {
            $dto     = UpdateUsuarioDTO::fromRequest($request);
            $usuario = $this->usuarioRepository->update($id, $dto);

            return $this->successResponse(
                data: new UsuarioResource($usuario),
                message: 'Usuario actualizado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al actualizar el usuario.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Elimina un usuario.
     *
     * DELETE /api/usuarios/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->usuarioRepository->destroy($id);

            return $this->successResponse(
                message: 'Usuario eliminado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al eliminar el usuario.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    // ─── Cajas ────────────────────────────────────────────────────────────────

    /**
     * Retorna las cajas asignadas a un usuario.
     *
     * GET /api/usuarios/{id}/cajas
     */
    public function getCajas(int $id): JsonResponse
    {
        try {
            $cajas = $this->usuarioRepository->getCajas($id);

            $data = $cajas->map(fn ($uc) => [
                'id'          => $uc->id,
                'cajaId'      => $uc->caja_id,
                'cajaNombre'  => $uc->caja?->caja_nombre,
                'localNombre' => $uc->caja?->local?->loca_nombre,
                'habilitado'  => $uc->usca_habilitado,
                'fechaInicio' => $uc->usca_fecha_inicio?->format('Y-m-d'),
            ])->values();

            return $this->successResponse(
                data: $data,
                message: 'Cajas del usuario obtenidas correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener las cajas del usuario.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Asigna una caja a un usuario.
     *
     * POST /api/usuarios/{id}/cajas
     * Body: { caja_id, usca_habilitado?, usca_fecha_inicio? }
     */
    public function asignarCaja(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'caja_id'           => ['required', 'integer', 'exists:cajas,id'],
                'usca_habilitado'   => ['sometimes', 'boolean'],
                'usca_fecha_inicio' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            ]);

            $uc = $this->usuarioRepository->asignarCaja(
                usuaId:      $id,
                cajaId:      (int) $request->caja_id,
                habilitado:  (bool) ($request->usca_habilitado ?? true),
                fechaInicio: $request->usca_fecha_inicio,
            );

            return $this->successResponse(
                data: [
                    'id'          => $uc->id,
                    'cajaId'      => $uc->caja_id,
                    'cajaNombre'  => $uc->caja?->caja_nombre,
                    'localNombre' => $uc->caja?->local?->loca_nombre,
                    'habilitado'  => $uc->usca_habilitado,
                    'fechaInicio' => $uc->usca_fecha_inicio?->format('Y-m-d'),
                ],
                message: 'Caja asignada correctamente.',
                statusCode: 201,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al asignar la caja.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Desvincula una caja de un usuario.
     *
     * DELETE /api/usuarios/{id}/cajas/{cajaId}
     */
    public function quitarCaja(int $id, int $cajaId): JsonResponse
    {
        try {
            $this->usuarioRepository->quitarCaja($id, $cajaId);

            return $this->successResponse(
                message: 'Caja desvinculada correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al desvincular la caja.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Activa/desactiva la caja asignada a un usuario.
     *
     * PATCH /api/usuarios/{id}/cajas/{cajaId}/toggle
     */
    public function toggleCaja(int $id, int $cajaId): JsonResponse
    {
        try {
            $uc = $this->usuarioRepository->toggleCaja($id, $cajaId);

            return $this->successResponse(
                data: [
                    'id'          => $uc->id,
                    'cajaId'      => $uc->caja_id,
                    'cajaNombre'  => $uc->caja?->caja_nombre,
                    'localNombre' => $uc->caja?->local?->loca_nombre,
                    'habilitado'  => $uc->usca_habilitado,
                    'fechaInicio' => $uc->usca_fecha_inicio?->format('Y-m-d'),
                ],
                message: 'Estado de la caja actualizado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al cambiar el estado de la caja.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
