<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (públicas)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas con JWT
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function (): void {
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });

    // Dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
        Route::get('data', [DashboardController::class, 'dataDashboard'])->name('data');
    });

    // Cajas
    Route::prefix('cajas')->name('cajas.')->group(function (): void {
        Route::get('autocomplete', [CajaController::class, 'autocomplete'])->name('autocomplete');
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::get('{id}', [CajaController::class, 'show'])->name('show');
    });

    // Productos
    Route::prefix('productos')->name('productos.')->group(function (): void {
        Route::get('autocomplete', [ProductoController::class, 'autocomplete'])->name('autocomplete');
    });

    // Movimientos
    Route::prefix('movimientos')->name('movimientos.')->group(function (): void {
        Route::get('table', [MovimientoController::class, 'table'])->name('table');
        Route::post('/', [MovimientoController::class, 'store'])->name('store');
        Route::get('{id}', [MovimientoController::class, 'show'])->name('show');
        Route::delete('{id}', [MovimientoController::class, 'destroy'])->name('destroy');
    });

    // Roles
    Route::prefix('roles')->name('roles.')->group(function (): void {
        Route::get('autocomplete', [RolController::class, 'autocomplete'])->name('autocomplete');
    });

    // Usuarios
    Route::prefix('usuarios')->name('usuarios.')->group(function (): void {
        Route::get('plantilla', [UsuarioController::class, 'plantilla'])->name('plantilla');
        Route::get('exportar', [UsuarioController::class, 'exportar'])->name('exportar');
        Route::post('importar', [UsuarioController::class, 'importar'])->name('importar');
        Route::get('table', [UsuarioController::class, 'table'])->name('table');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('{id}', [UsuarioController::class, 'show'])->name('show');
        Route::put('{id}', [UsuarioController::class, 'update'])->name('update');
        Route::delete('{id}', [UsuarioController::class, 'destroy'])->name('destroy');
        // Cajas del usuario
        Route::get('{id}/cajas', [UsuarioController::class, 'getCajas'])->name('cajas.index');
        Route::post('{id}/cajas', [UsuarioController::class, 'asignarCaja'])->name('cajas.asignar');
        Route::delete('{id}/cajas/{cajaId}', [UsuarioController::class, 'quitarCaja'])->name('cajas.quitar');
        Route::patch('{id}/cajas/{cajaId}/toggle', [UsuarioController::class, 'toggleCaja'])->name('cajas.toggle');
    });
});
