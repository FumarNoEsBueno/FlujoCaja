<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use App\Repositories\Dashboard\Interfaces\DashboardRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {}

    public function dataDashboard(): JsonResponse
    {
        try {
            $usuaId = Auth::id();
            $data = $this->dashboardRepository->getData($usuaId);

            return $this->successResponse($data, 'Datos del dashboard obtenidos correctamente');
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al obtener los datos del dashboard',
                500,
                $e,
                __METHOD__,
            );
        }
    }
}
