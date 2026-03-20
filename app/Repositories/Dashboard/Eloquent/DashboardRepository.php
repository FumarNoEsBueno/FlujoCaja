<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard\Eloquent;

use App\Models\Movimiento;
use App\Models\ProductosDelMovimiento;
use App\Models\UsuariosPorCaja;
use App\Repositories\Dashboard\Interfaces\DashboardRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Retorna todos los datos necesarios para el dashboard del usuario.
     */
    public function getData(int $usuaId): array
    {
        $hoy = Carbon::today();

        // Cajas asignadas al usuario (máximo 10, habilitadas)
        $cajasIds = UsuariosPorCaja::where('usua_id', $usuaId)
            ->where('usca_habilitado', true)
            ->with(['caja.local'])
            ->get();

        $cajaIds = $cajasIds->pluck('caja_id')->toArray();

        $movimientosDeHoy = Movimiento::whereIn('caja_id', $cajaIds)
            ->whereDate('movi_fecha_ingreso', $hoy)
            ->get();

        // ─── Ventas Hoy ───────────────────────────────────────────────────────────
        $ventasHoy = $movimientosDeHoy->sum('movi_monto_total');

        // ─── Movimientos por caja (hoy) ──────────────────────────────────────────
        $movimientosPorCaja = $movimientosDeHoy->groupBy('caja_id')->map(function ($movimientos) {
            return [
                'total_movimientos' => $movimientos->count(),
            ];
        });
    
        // ─── Monto total por caja (acumulado total) ───────────────────────────────
        $montoPorCaja = Movimiento::select('caja_id', DB::raw('SUM(movi_monto_total) as monto_total'))
            ->whereIn('caja_id', $cajaIds)
            ->groupBy('caja_id')
            ->get()
            ->keyBy('caja_id');

        // ─── Cajas con info completa ──────────────────────────────────────────────
        $cajas = $cajasIds->map(function ($usuarioCaja) use ($movimientosPorCaja, $montoPorCaja) {
            $caja = $usuarioCaja->caja;
            $cajaId = $caja->id;

            return [
                'cajaId' => $cajaId,
                'cajaNombre' => $caja->caja_nombre,
                'localNombre' => $caja->local?->loca_nombre ?? 'Sin local',
                'totalMovimientos' => (int) ($movimientosPorCaja[$cajaId]?->total_movimientos ?? 0),
                'montoTotal' => (float) ($montoPorCaja[$cajaId]?->monto_total ?? 0),
            ];
        })->values()->toArray();

        $productosCalculados = [];

        // ─── Productos vendidos hoy ───────────────────────────────────────────────
        ProductosDelMovimiento::select(
            'p.prod_nombre as prodNombre',
            'p.id as prodId',
            'productos_del_movimiento.pdmo_cantidad as cantidad'
        )
            ->join('movimientos', 'productos_del_movimiento.movi_id', '=', 'movimientos.id')
            ->join('producto as p', 'productos_del_movimiento.prod_id', '=', 'p.id')
            ->whereDate('movi_fecha_ingreso', $hoy)
            ->get()->map(function ($item) use (&$productosCalculados) {
                $productosCalculados[$item->prodId]['prodNombre'] = $item->prodNombre;
                $productosCalculados[$item->prodId]['totalVendido'] = ($productosCalculados[$item->prodId]['cantidad'] ?? 0) + $item->cantidad;
                $productosCalculados[$item->prodId]['prodId'] = $item->prodId;
            });

        return [
            'ventasHoy' => $ventasHoy,
            'cajas' => $cajas,
            'productosDia' => array_values($productosCalculados),
        ];
    }
}
