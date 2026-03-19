<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\MovimientoImportErrorsMail;
use App\Models\Caja;
use App\Models\Movimiento;
use App\Models\TipoMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MovimientoExcelService
{
    // ─── Columnas de la plantilla de importación (en orden) ─────────────────

    private const HEADERS_IMPORT = [
        'A' => 'Fecha',
        'B' => 'Monto',
        'C' => 'Medio de pago',
        'D' => 'Propina',
        'E' => 'Caja',
        'F' => 'Descripción',
    ];

    private const EXAMPLE_ROW = [
        '15/03/2026',
        '85000',
        'Débito',
        '500',
        'Caja Principal',
        'Compra de productos varios',
    ];

    // ─── Generar plantilla ────────────────────────────────────────────────────

    public function generarPlantilla(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Movimientos');

        // ── Fila 1: instrucciones ──────────────────────────────────────────
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1',
            'PLANTILLA DE IMPORTACIÓN DE MOVIMIENTOS — Completá los datos desde la fila 4. ' .
            'La fila 3 es solo un ejemplo. Fecha: DD/MM/AAAA. ' .
            'Caja debe ser exactamente el nombre de una caja registrada en el sistema.'
        );
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // ── Fila 2: headers ───────────────────────────────────────────────
        foreach (self::HEADERS_IMPORT as $col => $label) {
            $sheet->setCellValue($col . '2', $label);
        }
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // ── Fila 3: ejemplo ───────────────────────────────────────────────
        $colLetters = array_keys(self::HEADERS_IMPORT);
        foreach (self::EXAMPLE_ROW as $idx => $value) {
            $sheet->setCellValue($colLetters[$idx] . '3', $value);
        }
        // Forzar la fecha como texto para que no la interprete Excel
        $sheet->getCell('A3')->setValueExplicit('15/03/2026', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->getStyle('A3:F3')->applyFromArray([
            'font'      => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0f9ff']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
        ]);
        $sheet->setCellValue('A3', '(EJEMPLO) 15/03/2026');
        $sheet->getRowDimension(3)->setRowHeight(22);

        // ── Filas de datos (4-103): zona de ingreso ───────────────────────
        $sheet->getStyle('A4:F103')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
        ]);
        for ($row = 4; $row <= 103; $row += 2) {
            $sheet->getStyle("A{$row}:F{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('f8fafc');
        }

        // ── Anchos de columna ─────────────────────────────────────────────
        $widths = ['A' => 16, 'B' => 14, 'C' => 18, 'D' => 12, 'E' => 22, 'F' => 32];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A3');

        // ── Escribir a archivo temporal y servir ──────────────────────────
        $writer   = new Xlsx($spreadsheet);
        $filename = 'plantilla_movimientos_' . now()->format('Ymd') . '.xlsx';
        $tmpPath  = tempnam(sys_get_temp_dir(), 'plantilla_') . '.xlsx';
        $writer->save($tmpPath);

        return new StreamedResponse(function () use ($tmpPath): void {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => filesize($tmpPath),
            'Cache-Control'       => 'no-cache',
        ]);
    }

    // ─── Exportar a Excel ─────────────────────────────────────────────────────

    /**
     * Genera un Excel con los movimientos filtrados.
     *
     * @param  array{desde?: string, hasta?: string}  $filters
     * @param  int|null  $limite  null = todos
     */
    public function exportar(array $filters, ?int $limite): StreamedResponse
    {
        $query = Movimiento::with(['usuario', 'caja', 'productosDelMovimiento'])
            ->when($filters['desde'] ?? null, fn ($q, $v) =>
                $q->whereDate('movi_fecha_ingreso', '>=', $v)
            )
            ->when($filters['hasta'] ?? null, fn ($q, $v) =>
                $q->whereDate('movi_fecha_ingreso', '<=', $v)
            )
            ->orderBy('movi_fecha_ingreso', 'desc');

        $movimientos = $limite
            ? $query->limit($limite)->get()
            : $query->get();

        // ── Construir Excel ───────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Movimientos');

        // Fila 1: headers
        $headers = ['Fecha', 'Monto declarado', 'Suma de productos', 'Usuario', 'Descripción', 'Medio de pago', 'Caja', 'Propina'];
        foreach ($headers as $idx => $label) {
            $col = chr(65 + $idx); // A, B, C...
            $sheet->setCellValue("{$col}1", $label);
        }
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Filas de datos
        foreach ($movimientos as $rowIdx => $movimiento) {
            $excelRow = $rowIdx + 2;

            $sumaProductos = $movimiento->productosDelMovimiento
                ->sum(fn ($p) => $p->pdmo_cantidad * $p->pdmo_monto_unitario);

            $nombreUsuario = $movimiento->usuario
                ? trim($movimiento->usuario->usua_nombre . ' ' . $movimiento->usuario->usua_apellido_p)
                : '—';

            $sheet->getCell("A{$excelRow}")->setValueExplicit(
                $movimiento->movi_fecha_ingreso?->format('d/m/Y') ?? '—',
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->setCellValue("B{$excelRow}", $movimiento->movi_monto_total ?? 0);
            $sheet->setCellValue("C{$excelRow}", $sumaProductos);
            $sheet->setCellValue("D{$excelRow}", $nombreUsuario);
            $sheet->setCellValue("E{$excelRow}", $movimiento->movi_descripcion ?? '—');
            $sheet->setCellValue("F{$excelRow}", $movimiento->movi_medio_pago ?? '—');
            $sheet->setCellValue("G{$excelRow}", $movimiento->caja?->caja_nombre ?? '—');
            $sheet->setCellValue("H{$excelRow}", $movimiento->movi_propina ?? 0);

            // Zebra striping
            $bgColor = $rowIdx % 2 === 0 ? 'FFFFFF' : 'f8fafc';
            $sheet->getStyle("A{$excelRow}:H{$excelRow}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            ]);
        }

        // Anchos de columna
        $widths = ['A' => 14, 'B' => 16, 'C' => 18, 'D' => 26, 'E' => 32, 'F' => 16, 'G' => 20, 'H' => 12];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A2');

        $writer   = new Xlsx($spreadsheet);
        $filename = 'movimientos_' . now()->format('Ymd_His') . '.xlsx';
        $tmpPath  = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';
        $writer->save($tmpPath);

        return new StreamedResponse(function () use ($tmpPath): void {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => filesize($tmpPath),
            'Cache-Control'       => 'no-cache',
        ]);
    }

    // ─── Importar desde archivo ───────────────────────────────────────────────

    /**
     * @return array{importados: int, errores: int, total: int}
     */
    public function importar(string $rutaArchivo, string $nombreArchivo, string $emailDestino, int $usuaId): array
    {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Fila 1: instrucciones, fila 2: headers, fila 3: ejemplo
        // Datos desde índice 3 (base 0) → fila 4 del Excel
        $dataRows = array_slice($rows, 3);

        // Resolución del timo_id una sola vez
        /** @var TipoMovimiento $tipoMovimiento */
        $tipoMovimiento = TipoMovimiento::where('timo_nombre', 'Pago Transbank')->firstOrFail();

        // Lookup de cajas por nombre (lowercase) una sola vez
        /** @var array<string, int> $cajasMap  nombre_lowercase => id */
        $cajasMap = Caja::all(['id', 'caja_nombre'])
            ->mapWithKeys(fn ($c) => [strtolower(trim($c->caja_nombre)) => $c->id])
            ->toArray();

        $importados  = 0;
        $erroresList = [];
        $filaExcel   = 4;

        foreach ($dataRows as $row) {
            if ($this->filaVacia($row)) {
                $filaExcel++;
                continue;
            }

            [$fecha, $monto, $medioPago, $propina, $cajaNombre, $descripcion] = array_pad($row, 6, null);

            // Normalizar strings
            $fecha       = $this->str($fecha);
            $monto       = $this->str($monto);
            $medioPago   = $this->str($medioPago);
            $propina     = $this->str($propina);
            $cajaNombre  = $this->str($cajaNombre);
            $descripcion = $this->str($descripcion);

            // Convertir fecha DD/MM/YYYY → Y-m-d
            $fechaConvertida = $this->convertirFecha($fecha);

            // Buscar caja_id por nombre
            $cajaId = $cajasMap[strtolower($cajaNombre)] ?? null;

            // Preparar datos para validación
            $data = [
                'movi_fecha_ingreso'   => $fechaConvertida,
                'movi_monto_total'     => $monto !== '' ? $monto : null,
                'movi_medio_pago'      => $medioPago ?: null,
                'movi_propina'         => $propina !== '' ? $propina : 0,
                'caja_id'              => $cajaId,
                'movi_descripcion'     => $descripcion ?: null,
            ];

            $validator = Validator::make($data, [
                'movi_fecha_ingreso' => ['required', 'date_format:Y-m-d'],
                'movi_monto_total'   => ['required', 'numeric', 'min:0'],
                'movi_medio_pago'    => ['nullable', 'string', 'max:45'],
                'movi_propina'       => ['nullable', 'integer', 'min:0'],
                'caja_id'            => ['required', 'integer'],
                'movi_descripcion'   => ['nullable', 'string', 'max:255'],
            ], $this->mensajes($cajaNombre, $fecha));

            if ($validator->fails()) {
                $erroresList[] = [
                    'fila'    => $filaExcel,
                    'datos'   => implode(' | ', array_filter([$fecha, $monto, $medioPago, $cajaNombre])),
                    'errores' => $validator->errors()->all(),
                ];
                $filaExcel++;
                continue;
            }

            // Crear movimiento
            Movimiento::create([
                'movi_id_transaccion' => (string) Str::uuid(),
                'movi_fecha_ingreso'  => $fechaConvertida,
                'movi_monto_total'    => (float) $monto,
                'movi_medio_pago'     => $medioPago ?: null,
                'movi_propina'        => (int) ($propina ?: 0),
                'movi_descripcion'    => $descripcion ?: null,
                'timo_id'             => $tipoMovimiento->id,
                'usua_id'             => $usuaId,
                'caja_id'             => $cajaId,
            ]);

            $importados++;
            $filaExcel++;
        }

        $totalFilas = $importados + count($erroresList);

        if (count($erroresList) > 0) {
            Mail::to($emailDestino)->send(new MovimientoImportErrorsMail(
                nombreArchivo: $nombreArchivo,
                totalFilas:    $totalFilas,
                importados:    $importados,
                errores:       $erroresList,
            ));
        }

        return [
            'importados' => $importados,
            'errores'    => count($erroresList),
            'total'      => $totalFilas,
        ];
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function filaVacia(array $row): bool
    {
        return empty(array_filter($row, fn ($v) => $v !== null && $v !== ''));
    }

    private function str(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function convertirFecha(string $fecha): string
    {
        if ($fecha === '') {
            return '';
        }

        foreach (['d/m/Y', 'd-m-Y'] as $formato) {
            try {
                $carbon = Carbon::createFromFormat($formato, $fecha);
                if ($carbon && $carbon->format($formato) === $fecha) {
                    return $carbon->format('Y-m-d');
                }
            } catch (\Throwable) {
                // ignorar y probar el siguiente
            }
        }

        return $fecha;
    }

    /** @return array<string, string> */
    private function mensajes(string $cajaNombre, string $fechaOriginal): array
    {
        return [
            'movi_fecha_ingreso.required'    => 'La fecha es obligatoria.',
            'movi_fecha_ingreso.date_format' => "La fecha \"{$fechaOriginal}\" no es válida. Usá el formato DD/MM/AAAA.",
            'movi_monto_total.required'      => 'El monto es obligatorio.',
            'movi_monto_total.numeric'       => 'El monto debe ser un número.',
            'movi_monto_total.min'           => 'El monto no puede ser negativo.',
            'caja_id.required'               => "La caja \"{$cajaNombre}\" no existe. Usá exactamente el nombre de una caja registrada.",
            'caja_id.integer'                => "La caja \"{$cajaNombre}\" no existe. Usá exactamente el nombre de una caja registrada.",
        ];
    }
}
