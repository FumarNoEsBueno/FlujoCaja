<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard\Interfaces;

interface DashboardRepositoryInterface
{
    public function getData(int $usuaId): array;
}
