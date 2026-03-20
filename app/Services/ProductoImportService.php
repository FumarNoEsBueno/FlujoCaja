<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ProductoImportMail;
use App\Models\Producto;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductoImportService
{
    // ─── Columnas del Excel (en orden) ───────────────────────────────────────

    private const HEADERS = [
        'A' => 'Nombre',
        'B' => 'Precio',
    ];

    private const EXAMPLE_ROW = [
        'Empanada de pino',
        '1500',
    ];

    // ─── Generar plantilla ────────────────────────────────────────────────────

    public function generarPlantilla(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos');

        // ── Fila 1: instrucciones ──────────────────────────────────────────
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1',
            'PLANTILLA DE IMPORTACIÓN DE PRODUCTOS — Completá los datos desde la fila 3. '.
            'La fila 2 es solo un ejemplo. Precio debe ser un número positivo.'
        );
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // ── Fila 2: headers ───────────────────────────────────────────────
        foreach (self::HEADERS as $col => $label) {
            $cell = $col.'2';
            $sheet->setCellValue($cell, $label);
        }
        $sheet->getStyle('A2:B2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // ── Fila 3: ejemplo ───────────────────────────────────────────────
        $colLetters = array_keys(self::HEADERS);
        foreach (self::EXAMPLE_ROW as $idx => $value) {
            $sheet->setCellValue($colLetters[$idx].'3', $value);
        }
        $sheet->setCellValue('A3', '(EJEMPLO) Empanada de pino');
        $sheet->getStyle('A3:B3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0f9ff']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // ── Filas de datos (4-103): zona de ingreso ───────────────────────
        $sheet->getStyle('A4:B103')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
        ]);
        for ($row = 4; $row <= 103; $row += 2) {
            $sheet->getStyle("A{$row}:B{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('f8fafc');
        }

        // ── Anchos de columna ─────────────────────────────────────────────
        $widths = ['A' => 36, 'B' => 16];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Congelar paneles ──────────────────────────────────────────────
        $sheet->freezePane('A3');

        // ── Escribir a archivo temporal y servir como response ───────────
        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_productos_'.now()->format('Ymd').'.xlsx';
        $tmpPath = tempnam(sys_get_temp_dir(), 'plantilla_').'.xlsx';
        $writer->save($tmpPath);

        return new StreamedResponse(function () use ($tmpPath): void {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => filesize($tmpPath),
            'Cache-Control' => 'no-cache',
        ]);
    }

    // ─── Exportar a Excel ─────────────────────────────────────────────────────

    /**
     * Genera un Excel con los productos filtrados.
     *
     * @param  array{nombre?: string}  $filters
     * @param  'asc'|'desc'  $orden
     * @param  int|null  $limite  null = todos
     */
    public function exportar(array $filters, string $orden, ?int $limite): StreamedResponse
    {
        $query = Producto::when($filters['nombre'] ?? null, fn ($q, $v) => $q->where('prod_nombre', 'LIKE', "%{$v}%")
        )
            ->orderBy('prod_nombre', $orden);

        $productos = $limite
            ? $query->limit($limite)->get()
            : $query->get();

        // ── Construir Excel ───────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos');

        // Fila 1: headers
        $headers = ['ID', 'Nombre', 'Precio'];
        foreach ($headers as $idx => $label) {
            $col = chr(65 + $idx);
            $sheet->setCellValue("{$col}1", $label);
        }
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Filas de datos
        foreach ($productos as $rowIdx => $producto) {
            $excelRow = $rowIdx + 2;

            $sheet->setCellValue("A{$excelRow}", $producto->id);
            $sheet->setCellValue("B{$excelRow}", $producto->prod_nombre);
            $sheet->setCellValue("C{$excelRow}", $producto->prod_precio);

            $bgColor = $rowIdx % 2 === 0 ? 'FFFFFF' : 'f8fafc';
            $sheet->getStyle("A{$excelRow}:C{$excelRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            ]);
        }

        // Anchos de columna
        $widths = ['A' => 8, 'B' => 36, 'C' => 14];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'productos_'.now()->format('Ymd_His').'.xlsx';
        $tmpPath = tempnam(sys_get_temp_dir(), 'export_').'.xlsx';
        $writer->save($tmpPath);

        return new StreamedResponse(function () use ($tmpPath): void {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => filesize($tmpPath),
            'Cache-Control' => 'no-cache',
        ]);
    }

    // ─── Importar desde archivo ───────────────────────────────────────────────

    /**
     * @return array{importados: int, errores: int, total: int}
     */
    public function importar(string $rutaArchivo, string $nombreArchivo, string $emailDestino): array
    {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        // Saltamos fila 1 (instrucciones), fila 2 (headers), fila 3 (ejemplo)
        // Los datos empiezan en el índice 3 (base 0) → fila 4 del Excel
        $dataRows = array_slice($rows, 3);

        $importados = 0;
        $erroresList = [];
        $filaExcel = 4;

        foreach ($dataRows as $row) {
            if ($this->filaVacia($row)) {
                $filaExcel++;

                continue;
            }

            [$nombre, $precio] = array_pad($row, 2, null);

            $nombre = $this->str($nombre);
            $precio = $this->str($precio);

            $data = [
                'prod_nombre' => $nombre,
                'prod_precio' => $precio !== '' ? $precio : null,
            ];

            $validator = Validator::make($data, [
                'prod_nombre' => ['required', 'string', 'max:100'],
                'prod_precio' => ['required', 'numeric', 'min:0'],
            ], $this->mensajes());

            if ($validator->fails()) {
                $erroresList[] = [
                    'fila' => $filaExcel,
                    'datos' => implode(' | ', array_filter([$nombre, $precio])),
                    'errores' => $validator->errors()->all(),
                ];
                $filaExcel++;

                continue;
            }

            Producto::create([
                'prod_nombre' => $nombre,
                'prod_precio' => (float) $precio,
            ]);

            $importados++;
            $filaExcel++;
        }

        $totalFilas = $importados + count($erroresList);

        // Enviar correo siempre (éxito o error)
        Mail::to($emailDestino)->send(new ProductoImportMail(
            nombreArchivo: $nombreArchivo,
            totalFilas: $totalFilas,
            importados: $importados,
            errores: $erroresList,
        ));

        return [
            'importados' => $importados,
            'errores' => count($erroresList),
            'total' => $totalFilas,
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

    /** @return array<string, string> */
    private function mensajes(): array
    {
        return [
            'prod_nombre.required' => 'El nombre del producto es obligatorio.',
            'prod_nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'prod_precio.required' => 'El precio es obligatorio.',
            'prod_precio.numeric' => 'El precio debe ser un número.',
            'prod_precio.min' => 'El precio debe ser mayor o igual a 0.',
        ];
    }
}
