<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Movimiento\StoreMovimientoRequest;
use App\Http\Resources\MovimientoResource;
use App\Http\Traits\ApiResponser;
use App\Models\TipoMovimiento;
use App\Repositories\Movimiento\DTOs\StoreMovimientoDTO;
use App\Repositories\Movimiento\Interfaces\MovimientoRepositoryInterface;
use App\Services\MovimientoExcelService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MovimientoController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly MovimientoRepositoryInterface $movimientoRepository,
        private readonly MovimientoExcelService        $excelService,
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

    // ─── Excel ────────────────────────────────────────────────────────────────

    /**
     * Descarga la plantilla Excel para importación de movimientos.
     *
     * GET /api/movimientos/plantilla
     */
    public function plantilla(): StreamedResponse
    {
        try {
            return $this->excelService->generarPlantilla();
        } catch (Exception $e) {
            Log::error('Error al generar plantilla de movimientos', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Exporta movimientos a Excel con filtros opcionales.
     *
     * GET /api/movimientos/exportar
     * Query params: desde?, hasta?, todos (bool), limite (int)
     */
    public function exportar(Request $request): StreamedResponse|JsonResponse
    {
        $request->validate([
            'desde'  => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'hasta'  => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'limite' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        // 'todos' viene como string "true"/"false" desde query params GET
        $todos = filter_var($request->input('todos', 'false'), FILTER_VALIDATE_BOOLEAN);

        if (! $todos && ! $request->filled('limite')) {
            return response()->json([
                'message' => 'El campo límite es obligatorio cuando no se exportan todos los registros.',
                'errors'  => ['limite' => ['El límite es requerido.']],
            ], 422);
        }

        try {
            $filters = array_filter([
                'desde' => $request->input('desde'),
                'hasta' => $request->input('hasta'),
            ], fn ($v) => $v !== null && $v !== '');

            $limite = $todos ? null : (int) $request->input('limite');

            return $this->excelService->exportar($filters, $limite);
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al generar el Excel de exportación.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Importa movimientos desde un archivo Excel.
     * El usuario se toma del JWT. Si hay errores, envía correo de reporte.
     *
     * POST /api/movimientos/importar
     * Body: multipart/form-data — archivo: file (xlsx), email_reporte: string
     */
    public function importar(Request $request): JsonResponse
    {
        $request->validate([
            'archivo'       => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'email_reporte' => ['required', 'email'],
        ]);

        try {
            $usuaId        = (int) Auth::guard('api')->id();
            $archivo       = $request->file('archivo');
            $nombreArchivo = $archivo->getClientOriginalName();
            $rutaTemporal  = $archivo->getRealPath();

            $resultado = $this->excelService->importar(
                rutaArchivo:   $rutaTemporal,
                nombreArchivo: $nombreArchivo,
                emailDestino:  $request->email_reporte,
                usuaId:        $usuaId,
            );

            $mensaje = $resultado['errores'] > 0
                ? "Se importaron {$resultado['importados']} de {$resultado['total']} movimientos. Se enviaron los errores a {$request->email_reporte}."
                : "Se importaron {$resultado['importados']} movimientos correctamente.";

            return $this->successResponse(
                data: $resultado,
                message: $mensaje,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al procesar el archivo. Verificá que sea una planilla válida.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
