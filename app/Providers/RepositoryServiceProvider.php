<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Auth\Eloquent\AuthRepository;
use App\Repositories\Auth\Interfaces\AuthRepositoryInterface;
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
    }
}
