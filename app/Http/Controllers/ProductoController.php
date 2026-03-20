<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Http\Traits\ApiResponser;
use App\Models\Producto;
use App\Repositories\Producto\DTOs\StoreProductoDTO;
use App\Repositories\Producto\DTOs\UpdateProductoDTO;
use App\Repositories\Producto\Interfaces\ProductoRepositoryInterface;
use App\Services\ProductoImportService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductoController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly ProductoRepositoryInterface $productoRepository,
        private readonly ProductoImportService $productoImportService,
    ) {}

    /**
     * Lista productos paginados con filtros opcionales.
     *
     * GET /api/productos/table
     * Query params: nombre
     */
    public function table(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['nombre']);

            $paginator = $this->productoRepository->table($filters);

            $items = ProductoResource::collection($paginator->getCollection());

            return $this->paginatedResponse(
                data: $items,
                meta: [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'has_more' => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                ],
                message: 'Productos obtenidos correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al obtener los productos.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Crea un nuevo producto.
     *
     * POST /api/productos
     */
    public function store(StoreProductoRequest $request): JsonResponse
    {
        try {
            $dto = StoreProductoDTO::fromRequest($request);
            $producto = $this->productoRepository->store($dto);

            return $this->successResponse(
                data: new ProductoResource($producto),
                message: 'Producto creado correctamente.',
                statusCode: 201,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al crear el producto.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Retorna el detalle de un producto.
     *
     * GET /api/productos/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $producto = $this->productoRepository->show($id);

            return $this->successResponse(
                data: new ProductoResource($producto),
                message: 'Producto obtenido correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Producto no encontrado.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Actualiza un producto existente.
     *
     * PUT /api/productos/{id}
     */
    public function update(UpdateProductoRequest $request, int $id): JsonResponse
    {
        try {
            $dto = UpdateProductoDTO::fromRequest($request);
            $producto = $this->productoRepository->update($id, $dto);

            return $this->successResponse(
                data: new ProductoResource($producto),
                message: 'Producto actualizado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al actualizar el producto.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Elimina un producto.
     *
     * DELETE /api/productos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productoRepository->destroy($id);

            return $this->successResponse(
                message: 'Producto eliminado correctamente.',
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al eliminar el producto.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }

    /**
     * Lista simplificada de productos para autocomplete.
     *
     * GET /api/productos/autocomplete
     */
    public function autocomplete(): JsonResponse
    {
        $productos = Producto::select(['id', 'prod_nombre', 'prod_precio'])
            ->orderBy('prod_nombre')
            ->get()
            ->map(fn (Producto $p) => [
                'id' => $p->id,
                'label' => $p->prod_nombre,
                'precio' => $p->prod_precio,
            ]);

        return $this->successResponse(
            data: $productos,
            message: 'Productos obtenidos correctamente.',
        );
    }

    // ─── Excel ────────────────────────────────────────────────────────────────

    /**
     * Descarga la plantilla Excel para importar productos.
     *
     * GET /api/productos/plantilla
     */
    public function plantilla(): StreamedResponse
    {
        return $this->productoImportService->generarPlantilla();
    }

    /**
     * Exporta productos a Excel con filtros opcionales.
     *
     * GET /api/productos/exportar
     * Query params: nombre, orden (asc|desc), limite
     */
    public function exportar(Request $request): StreamedResponse
    {
        $filters = $request->only(['nombre']);
        $orden = in_array($request->query('orden'), ['asc', 'desc']) ? $request->query('orden') : 'asc';
        $limite = $request->query('limite') ? (int) $request->query('limite') : null;

        return $this->productoImportService->exportar($filters, $orden, $limite);
    }

    /**
     * Importa productos desde un archivo Excel.
     *
     * POST /api/productos/importar
     * Body: archivo (file), email (string)
     */
    public function importar(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'email' => ['required', 'email'],
        ]);

        try {
            $archivo = $request->file('archivo');
            $nombreArchivo = $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->getRealPath();

            $resultado = $this->productoImportService->importar(
                rutaArchivo: $rutaArchivo,
                nombreArchivo: $nombreArchivo,
                emailDestino: $request->email,
            );

            $mensaje = $resultado['errores'] > 0
                ? "Importación completada con errores. {$resultado['importados']} importados, {$resultado['errores']} con error."
                : "Importación exitosa. {$resultado['importados']} productos importados.";

            return $this->successResponse(
                data: $resultado,
                message: $mensaje,
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error al procesar el archivo de importación.',
                statusCode: 500,
                exception: $e,
                method: __METHOD__,
            );
        }
    }
}
