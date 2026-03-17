<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Throwable;

trait ApiResponser
{
    /**
     * Respuesta exitosa estándar.
     *
     * @param  mixed  $data  Datos a incluir (Resource, array, null)
     * @param  string  $message  Mensaje descriptivo
     * @param  int  $statusCode  Código HTTP (200, 201, etc.)
     */
    protected function successResponse(
        mixed $data = null,
        string $message = '',
        int $statusCode = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Respuesta exitosa con paginación.
     *
     * @param  AnonymousResourceCollection|array<int, mixed>  $data  Colección paginada
     * @param  array<string, int>  $meta  Metadatos de paginación
     * @param  string  $message  Mensaje descriptivo
     */
    protected function paginatedResponse(
        AnonymousResourceCollection|array $data,
        array $meta,
        string $message = '',
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    /**
     * Respuesta de error estándar. Loguea automáticamente si se pasa excepción.
     *
     * @param  string  $message  Mensaje para el cliente
     * @param  int  $statusCode  Código HTTP (409, 422, 500, etc.)
     * @param  Throwable|null  $exception  Excepción original (se loguea, NO se expone al cliente)
     * @param  string  $method  Método donde ocurrió (para trazabilidad en logs)
     */
    protected function errorResponse(
        string $message,
        int $statusCode = 500,
        ?Throwable $exception = null,
        string $method = '',
    ): JsonResponse {
        if ($exception) {
            Log::error('API Error', [
                'message' => $exception->getMessage(),
                'method' => $method ?: static::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'status_code' => $statusCode,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }
}
