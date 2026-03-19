<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Auth\Eloquent\AuthRepository;
use App\Repositories\Auth\Interfaces\AuthRepositoryInterface;
use App\Repositories\Caja\Eloquent\CajaRepository;
use App\Repositories\Caja\Interfaces\CajaRepositoryInterface;
use App\Repositories\Dashboard\Eloquent\DashboardRepository;
use App\Repositories\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Repositories\Movimiento\Eloquent\MovimientoRepository;
use App\Repositories\Movimiento\Interfaces\MovimientoRepositoryInterface;
use App\Repositories\Usuario\Eloquent\UsuarioRepository;
use App\Repositories\Usuario\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registra todos los bindings Interface → Implementación.
     *
     * Convención de nombres para nuevos dominios:
     *   App\Repositories\{Dominio}\Interfaces\{Dominio}RepositoryInterface
     *   App\Repositories\{Dominio}\Eloquent\{Dominio}Repository
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(CajaRepositoryInterface::class, CajaRepository::class);
        $this->app->bind(MovimientoRepositoryInterface::class, MovimientoRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(UsuarioRepositoryInterface::class, UsuarioRepository::class);
    }
}
