<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\CajaResource;
use App\Http\Traits\ApiResponser;
use App\Models\Caja;
use App\Repositories\Caja\Interfaces\CajaRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly CajaRepositoryInterface $cajaRepository,
    ) {}

    /**
     * Retorna cajas del usuario para autocomplete (id + nombre).
     *
     * GET /api/cajas/autocomplete
     */
    public function autocomplete(): JsonResponse
    {
        try {
            $usuaId = (int) Auth::guard('api')->id();
            $cajas = $this->cajaRepository->autocomplete($usuaId);

            return $this->successResponse(
                data: $cajas->map(fn (Caja $c) => ['id' => $c->id, 'label' => $c->caja_nombre]),
                message: 'Cajas para autocomplete obtenidas correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener las cajas.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Lista todas las cajas habilitadas para el usuario autenticado.
     *
     * GET /api/cajas
     */
    public function index(): JsonResponse
    {
        try {
            $usuaId = (int) Auth::guard('api')->id();
            $cajas = $this->cajaRepository->index($usuaId);

            return $this->successResponse(
                data: CajaResource::collection($cajas),
                message: 'Cajas obtenidas correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener las cajas.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Retorna una caja específica.
     *
     * GET /api/cajas/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $caja = $this->cajaRepository->show($id);

            return $this->successResponse(
                data: new CajaResource($caja),
                message: 'Caja obtenida correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Caja no encontrada.',
                statusCode: 404,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
