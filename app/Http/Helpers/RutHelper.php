<?php

declare(strict_types=1);

namespace App\Http\Helpers;

final class RutHelper
{
    /**
     * Parsea un RUT chileno en cualquier formato y retorna sus partes.
     *
     * Formatos aceptados:
     *   - "12345678-9"   (con guión)
     *   - "12.345.678-9" (con puntos y guión)
     *   - "123456789"    (sin guión, DV al final)
     *   - "12345678-k"   (DV minúscula)
     *
     * @return array{
     *     rut: int,
     *     dv: string,
     *     numeroDocumento: string,
     *     formatted: string,
     *     raw: string,
     *     valid: bool
     * }
     */
    public static function parse(string $rut): array
    {
        // 1. Limpiar: sacar puntos, espacios y convertir a mayúsculas
        $cleaned = strtoupper(str_replace(['.', ' '], '', trim($rut)));

        // 2. Separar cuerpo y DV
        if (str_contains($cleaned, '-')) {
            [$body, $dv] = explode('-', $cleaned, 2);
        } else {
            // Sin guión: el último carácter es el DV
            $dv = substr($cleaned, -1);
            $body = substr($cleaned, 0, -1);
        }

        $rutNumber = (int) $body;

        // 3. numeroDocumento = cuerpo + DV sin formato (ej: "123456789")
        $numeroDocumento = $body.$dv;

        // 4. Formato visual con puntos y guión (ej: "12.345.678-9")
        $formatted = self::format($rutNumber, $dv);

        // 5. Validar dígito verificador
        $valid = self::validate($rutNumber, $dv);

        return [
            'rut' => $rutNumber,       // int:    12345678
            'dv' => $dv,               // string: "9" | "K"
            'numeroDocumento' => $numeroDocumento,  // string: "123456789"
            'formatted' => $formatted,        // string: "12.345.678-9"
            'raw' => $cleaned,          // string: "12345678-9" (limpio, sin puntos)
            'valid' => $valid,            // bool
        ];
    }

    /**
     * Valida si un RUT es válido usando el algoritmo módulo 11.
     */
    public static function validate(int $rut, string $dv): bool
    {
        $dv = strtoupper(trim($dv));

        $sum = 0;
        $factor = 2;
        $current = $rut;

        while ($current > 0) {
            $sum += ($current % 10) * $factor;
            $current = intdiv($current, 10);
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $remainder = 11 - ($sum % 11);

        $expected = match ($remainder) {
            11 => '0',
            10 => 'K',
            default => (string) $remainder,
        };

        return $expected === $dv;
    }

    /**
     * Formatea un número de RUT con puntos y guión.
     * Ej: 12345678, "9" → "12.345.678-9"
     */
    public static function format(int $rut, string $dv): string
    {
        return number_format($rut, 0, ',', '.').'-'.strtoupper($dv);
    }
}
