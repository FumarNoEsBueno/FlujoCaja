<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard\DTOs;

use Illuminate\Http\Request;

final readonly class StoreDashboardDTO
{
    public function __construct(
        // TODO: definir propiedades del DTO
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self;
        // TODO: mapear campos del request
    }
}
