<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Importación</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5;
            padding: 32px 16px;
            color: #18181b;
        }
        .wrapper {
            max-width: 620px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .header {
            background: #ef4444;
            padding: 28px 32px;
        }
        .header-icon { font-size: 32px; margin-bottom: 10px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .header p  { color: rgba(255,255,255,0.85); font-size: 14px; }
        .summary {
            display: flex;
            border-bottom: 1px solid #f4f4f5;
        }
        .stat {
            flex: 1;
            padding: 20px 24px;
            text-align: center;
            border-right: 1px solid #f4f4f5;
        }
        .stat:last-child { border-right: none; }
        .stat-number { font-size: 28px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
        .stat-label  { font-size: 12px; color: #71717a; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-number.ok    { color: #22c55e; }
        .stat-number.error { color: #ef4444; }
        .stat-number.total { color: #3b82f6; }
        .body { padding: 28px 32px; }
        .body h2 { font-size: 15px; font-weight: 600; color: #18181b; margin-bottom: 16px; }
        .error-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .error-table th {
            background: #f9fafb;
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        .error-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .error-table tr:last-child td { border-bottom: none; }
        .fila-badge {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .datos-cell { color: #374151; font-size: 12px; }
        .errores-list { list-style: none; }
        .errores-list li { color: #dc2626; font-size: 12px; padding: 1px 0; }
        .errores-list li::before { content: '• '; color: #f87171; }
        .nota {
            margin-top: 20px;
            padding: 14px 16px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 8px;
        }
        .nota p { font-size: 13px; color: #92400e; line-height: 1.5; }
        .footer {
            padding: 20px 32px;
            background: #fafafa;
            border-top: 1px solid #f4f4f5;
        }
        .footer p { font-size: 12px; color: #a1a1aa; line-height: 1.6; }
        .footer strong { color: #71717a; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            <div class="header">
                <div class="header-icon">⚠️</div>
                <h1>Importación con errores</h1>
                <p>Archivo: <strong>{{ $nombreArchivo }}</strong></p>
            </div>

            <div class="summary">
                <div class="stat">
                    <div class="stat-number total">{{ $totalFilas }}</div>
                    <div class="stat-label">Total filas</div>
                </div>
                <div class="stat">
                    <div class="stat-number ok">{{ $importados }}</div>
                    <div class="stat-label">Importados</div>
                </div>
                <div class="stat">
                    <div class="stat-number error">{{ count($errores) }}</div>
                    <div class="stat-label">Con error</div>
                </div>
            </div>

            <div class="body">
                <h2>Detalle de filas con error</h2>
                <table class="error-table">
                    <thead>
                        <tr>
                            <th>Fila</th>
                            <th>Datos ingresados</th>
                            <th>Motivo del error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errores as $error)
                            <tr>
                                <td><span class="fila-badge">Fila {{ $error['fila'] }}</span></td>
                                <td class="datos-cell">{{ $error['datos'] }}</td>
                                <td>
                                    <ul class="errores-list">
                                        @foreach($error['errores'] as $msg)
                                            <li>{{ $msg }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="nota">
                    <p>
                        💡 <strong>¿Qué hacer?</strong>
                        Corregí los datos indicados en las filas con error y volvé a importar
                        <strong>solo esas filas</strong> en una nueva planilla.
                        Las filas exitosas ya fueron importadas correctamente.
                    </p>
                </div>
            </div>

            <div class="footer">
                <p>
                    Este correo fue generado automáticamente por <strong>Flujo Caja</strong>.<br>
                    Importación realizada el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }} hrs.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
