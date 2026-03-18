<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Movimiento\StoreMovimientoRequest;
use App\Http\Resources\MovimientoResource;
use App\Http\Traits\ApiResponser;
use App\Models\TipoMovimiento;
use App\Repositories\Movimiento\DTOs\StoreMovimientoDTO;
use App\Repositories\Movimiento\Interfaces\MovimientoRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MovimientoController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly MovimientoRepositoryInterface $movimientoRepository,
    ) {}

    /**
     * Lista movimientos paginados con filtros opcionales.
     *
     * GET /api/movimientos/table
     * Query params: movi_id_transaccion, caja_id, usua_id, movi_fecha_ingreso
     */
    public function table(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['movi_id_transaccion', 'caja_id', 'usua_id', 'movi_fecha_ingreso']);

            $paginator = $this->movimientoRepository->table($filters);

            /** @var \Illuminate\Pagination\AbstractPaginator $paginator */
            $items = MovimientoResource::collection($paginator->getCollection());

            return $this->paginatedResponse(
                data: $items,
                meta: [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'has_more'     => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                ],
                message: 'Movimientos obtenidos correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener los movimientos.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Crea un nuevo movimiento.
     * El tipo de movimiento se fija a "Pago Transbank" automáticamente.
     * El usuario autenticado es asignado automáticamente.
     *
     * POST /api/movimientos
     */
    public function store(StoreMovimientoRequest $request): JsonResponse
    {
        try {
            $usuaId = (int) Auth::guard('api')->id();

            // "Pago Transbank" es el único tipo de movimiento por ahora
            /** @var TipoMovimiento $tipoMovimiento */
            $tipoMovimiento = TipoMovimiento::where('timo_nombre', 'Pago Transbank')->firstOrFail();

            $dto = StoreMovimientoDTO::fromRequest($request, $usuaId, $tipoMovimiento->id);

            $movimiento = $this->movimientoRepository->store($dto);

            return $this->successResponse(
                data: new MovimientoResource($movimiento),
                message: 'Movimiento creado correctamente.',
                statusCode: 201,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al crear el movimiento.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Retorna el detalle de un movimiento.
     *
     * GET /api/movimientos/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $movimiento = $this->movimientoRepository->show($id);

            return $this->successResponse(
                data: new MovimientoResource($movimiento),
                message: 'Movimiento obtenido correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Movimiento no encontrado.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Elimina un movimiento.
     *
     * DELETE /api/movimientos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->movimientoRepository->destroy($id);

            return $this->successResponse(
                message: 'Movimiento eliminado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al eliminar el movimiento.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
