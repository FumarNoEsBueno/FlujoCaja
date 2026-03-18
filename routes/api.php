<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProductoController;
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
});
