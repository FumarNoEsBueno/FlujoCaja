<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\UsuarioImportErrorsMail;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UsuarioImportService
{
    // ─── Columnas del Excel (en orden) ───────────────────────────────────────

    private const HEADERS = [
        'A' => 'Nombre',
        'B' => 'Apellido Paterno',
        'C' => 'Apellido Materno',
        'D' => 'RUT',
        'E' => 'Dígito Verificador',
        'F' => 'Correo',
        'G' => 'Fecha de Nacimiento',
        'H' => 'Contraseña',
        'I' => 'Rol',
    ];

    private const EXAMPLE_ROW = [
        'Juan',
        'Pérez',
        'González',
        '20194802',
        '9',
        'juan.perez@ejemplo.com',
        '25/03/1990',
        'clave123',
        'Trabajador',
    ];

    // ─── Generar plantilla ────────────────────────────────────────────────────

    public function generarPlantilla(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Usuarios');

        // ── Fila 1: instrucciones ──────────────────────────────────────────
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1',
            'PLANTILLA DE IMPORTACIÓN DE USUARIOS — Completá los datos desde la fila 3. '.
            'La fila 2 es solo un ejemplo. Fecha de nacimiento: DD/MM/AAAA. '.
            'Rol debe ser exactamente: "Administrador" o "Trabajador".'
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
        $sheet->getStyle('A2:I2')->applyFromArray([
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
        // Forzar la fecha como texto para que no la interprete Excel
        $sheet->getCell('G3')->setValueExplicit('25/03/1990', DataType::TYPE_STRING);

        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0f9ff']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
        ]);

        // Indicador visual "EJEMPLO" en la columna A fila 3
        $sheet->setCellValue('A3', '(EJEMPLO) Juan');
        $sheet->getRowDimension(3)->setRowHeight(22);

        // ── Filas de datos (4-103): zona de ingreso ───────────────────────
        $sheet->getStyle('A4:I103')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
        ]);
        // Filas pares con fondo levemente distinto
        for ($row = 4; $row <= 103; $row += 2) {
            $sheet->getStyle("A{$row}:I{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('f8fafc');
        }

        // ── Anchos de columna ─────────────────────────────────────────────
        $widths = ['A' => 18, 'B' => 18, 'C' => 18, 'D' => 14, 'E' => 10, 'F' => 28, 'G' => 18, 'H' => 18, 'I' => 16];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Congelar paneles para que los headers sean siempre visibles ───
        $sheet->freezePane('A3');

        // ── Escribir a archivo temporal y servir como response ───────────
        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_usuarios_'.now()->format('Ymd').'.xlsx';
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
     * Genera un Excel con los usuarios filtrados.
     *
     * @param  array{nombre?: string, rut?: string, role_id?: int}  $filters
     * @param  'asc'|'desc'  $orden  asc = primeros, desc = últimos
     * @param  int|null  $limite  null = todos
     */
    public function exportar(array $filters, string $orden, ?int $limite): StreamedResponse
    {
        $query = Usuario::with('rol')
            ->when($filters['nombre'] ?? null, fn ($q, $v) => $q->where(
                DB::raw("CONCAT(usua_nombre, ' ', usua_apellido_p)"),
                'LIKE',
                "%{$v}%"
            )
            )
            ->when($filters['rut'] ?? null, fn ($q, $v) => $q->where('usua_rut', 'LIKE', "%{$v}%")
            )
            ->when($filters['role_id'] ?? null, fn ($q, $v) => $q->where('role_id', $v)
            )
            ->orderBy('id', $orden);

        $usuarios = $limite
            ? $query->limit($limite)->get()
            : $query->get();

        // ── Construir Excel ───────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Usuarios');

        // Fila 1: headers
        $headers = ['ID', 'Nombre Completo', 'RUT', 'Correo', 'Rol', 'Fecha Nacimiento'];
        foreach ($headers as $idx => $label) {
            $col = chr(65 + $idx); // A, B, C...
            $sheet->setCellValue("{$col}1", $label);
        }
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Filas de datos
        foreach ($usuarios as $rowIdx => $usuario) {
            $excelRow = $rowIdx + 2;
            $nombreCompleto = trim(
                $usuario->usua_nombre.' '.
                $usuario->usua_apellido_p.
                ($usuario->usua_apellido_m ? ' '.$usuario->usua_apellido_m : '')
            );

            $sheet->setCellValue("A{$excelRow}", $usuario->id);
            $sheet->setCellValue("B{$excelRow}", $nombreCompleto);
            $sheet->setCellValue("C{$excelRow}", $usuario->usua_rut.'-'.$usuario->usua_dv);
            $sheet->setCellValue("D{$excelRow}", $usuario->usua_correo ?? '—');
            $sheet->setCellValue("E{$excelRow}", $usuario->rol?->role_nombre ?? '—');
            $sheet->getCell("F{$excelRow}")->setValueExplicit(
                $usuario->usua_fecha_nac?->format('d/m/Y') ?? '—',
                DataType::TYPE_STRING
            );

            // Zebra striping
            $bgColor = $rowIdx % 2 === 0 ? 'FFFFFF' : 'f8fafc';
            $sheet->getStyle("A{$excelRow}:F{$excelRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
            ]);
        }

        // Anchos de columna
        $widths = ['A' => 8, 'B' => 30, 'C' => 14, 'D' => 30, 'E' => 16, 'F' => 16];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'usuarios_'.now()->format('Ymd_His').'.xlsx';
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

        // Cargar roles una sola vez para lookup eficiente
        /** @var array<string, int> $rolesMap  nombre_lowercase => id */
        $rolesMap = Rol::all(['id', 'role_nombre'])
            ->mapWithKeys(fn ($r) => [strtolower(trim($r->role_nombre)) => $r->id])
            ->toArray();

        $importados = 0;
        $erroresList = [];
        $filaExcel = 4; // La primera fila de datos en el Excel

        foreach ($dataRows as $row) {
            // Ignorar filas completamente vacías
            if ($this->filaVacia($row)) {
                $filaExcel++;

                continue;
            }

            [$nombre, $apellidoP, $apellidoM, $rut, $dv, $correo, $fechaNac, $password, $rolNombre] = array_pad($row, 9, null);

            // Normalizar strings
            $nombre = $this->str($nombre);
            $apellidoP = $this->str($apellidoP);
            $apellidoM = $this->str($apellidoM);
            $rut = $this->str($rut);
            $dv = $this->str($dv);
            $correo = $this->str($correo);
            $fechaNac = $this->str($fechaNac);
            $password = $this->str($password);
            $rolNombre = $this->str($rolNombre);

            // Convertir fecha DD/MM/YYYY → Y-m-d
            $fechaConvertida = $this->convertirFecha($fechaNac);

            // Buscar role_id por nombre
            $roleId = $rolesMap[strtolower($rolNombre)] ?? null;

            // Validar con el Validator de Laravel
            $data = [
                'usua_nombre' => $nombre,
                'usua_apellido_p' => $apellidoP,
                'usua_apellido_m' => $apellidoM ?: null,
                'usua_rut' => $rut,
                'usua_dv' => $dv,
                'usua_correo' => $correo ?: null,
                'usua_fecha_nac' => $fechaConvertida,
                'usua_password' => $password,
                'role_id' => $roleId,
            ];

            $validator = Validator::make($data, [
                'usua_nombre' => ['required', 'string', 'max:45'],
                'usua_apellido_p' => ['required', 'string', 'max:45'],
                'usua_apellido_m' => ['nullable', 'string', 'max:45'],
                'usua_rut' => ['required', 'string', 'max:12'],
                'usua_dv' => ['required', 'string', 'max:1'],
                'usua_correo' => ['nullable', 'email', 'max:45', 'unique:usuarios,usua_correo'],
                'usua_fecha_nac' => ['required', 'date_format:Y-m-d'],
                'usua_password' => ['required', 'string', 'min:6'],
                'role_id' => ['required', 'integer'],
            ], $this->mensajes($rolNombre, $fechaNac));

            if ($validator->fails()) {
                $erroresList[] = [
                    'fila' => $filaExcel,
                    'datos' => implode(' | ', array_filter([$nombre, $apellidoP, $rut ? "{$rut}-{$dv}" : null, $correo])),
                    'errores' => $validator->errors()->all(),
                ];
                $filaExcel++;

                continue;
            }

            // Crear usuario
            Usuario::create([
                'usua_nombre' => $nombre,
                'usua_apellido_p' => $apellidoP,
                'usua_apellido_m' => $apellidoM ?: null,
                'usua_rut' => $rut,
                'usua_dv' => $dv,
                'usua_correo' => $correo ?: null,
                'usua_fecha_nac' => $fechaConvertida,
                'usua_password' => bcrypt($password),
                'role_id' => $roleId,
            ]);

            $importados++;
            $filaExcel++;
        }

        $totalFilas = $importados + count($erroresList);

        // Enviar correo si hubo errores
        if (count($erroresList) > 0) {
            Mail::to($emailDestino)->send(new UsuarioImportErrorsMail(
                nombreArchivo: $nombreArchivo,
                totalFilas: $totalFilas,
                importados: $importados,
                errores: $erroresList,
            ));
        }

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

    /**
     * Convierte DD/MM/YYYY o DD-MM-YYYY → Y-m-d.
     * Si el formato ya es Y-m-d, lo deja como está.
     * Retorna null-string si no puede parsear (el validator lo rechazará).
     */
    private function convertirFecha(string $fecha): string
    {
        if ($fecha === '') {
            return '';
        }

        // Intentar DD/MM/YYYY o DD-MM-YYYY
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

        // Si ya viene como Y-m-d lo devolvemos tal cual
        return $fecha;
    }

    /** @return array<string, string> */
    private function mensajes(string $rolNombre, string $fechaOriginal): array
    {
        return [
            'usua_nombre.required' => 'El nombre es obligatorio.',
            'usua_apellido_p.required' => 'El apellido paterno es obligatorio.',
            'usua_rut.required' => 'El RUT es obligatorio.',
            'usua_dv.required' => 'El dígito verificador es obligatorio.',
            'usua_correo.email' => 'El correo no es válido.',
            'usua_correo.unique' => 'El correo ya está registrado en el sistema.',
            'usua_fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'usua_fecha_nac.date_format' => "La fecha \"{$fechaOriginal}\" no es válida. Usá el formato DD/MM/AAAA.",
            'usua_password.required' => 'La contraseña es obligatoria.',
            'usua_password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'role_id.required' => "El rol \"{$rolNombre}\" no existe. Usá exactamente: Administrador o Trabajador.",
            'role_id.integer' => "El rol \"{$rolNombre}\" no existe. Usá exactamente: Administrador o Trabajador.",
        ];
    }
}
