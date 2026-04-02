<?php

declare(strict_types=1);

namespace App\Rules;

use App\Http\Helpers\RutHelper;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

final class RutValidator implements ValidationRule, DataAwareRule
{
    private array $data = [];

    /**
     * @param int|null    $rut  Cuerpo del RUT (sin DV)
     * @param string|null $dv   Dígito verificador
     */
    public function __construct(
        private readonly ?int    $rut    = null,
        private readonly ?string $dv     = null,
    ) {}

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }

    public function passes(string $attribute, mixed $value): bool
    {
        // ─── Modo 1: rut + dv pasados directamente por constructor ───
        if ($this->rut !== null && $this->dv !== null) {
            return RutHelper::validate($this->rut, $this->dv);
        }

        // ─── Modo 2: valor único "20194802-9" (nuevo frontend) ───
        if (is_string($value) && trim($value) !== '') {
            $parsed = RutHelper::parse($value);
            return $parsed['valid'];
        }

        // ─── Modo 3: campos separados en el request ───
        // Buscar el campo rut配偶 dv en los datos del request
        $rutField = str_replace('_dv', '_rut', $attribute);
        $rut = $this->data[$rutField] ?? null;
        $dv  = $this->data[$attribute] ?? null;

        if ($rut !== null && $dv !== null) {
            return RutHelper::validate((int) $rut, strtoupper(trim((string) $dv)));
        }

        return false;
    }

    public function message(): string
    {
        return 'El RUT no es válido según el algoritmo verificador (Regla 11).';
    }
}
