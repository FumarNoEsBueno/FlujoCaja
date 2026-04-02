<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de envío de correos</title>
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
            background: #22c55e;
            padding: 28px 32px;
        }
        .header-icon { font-size: 32px; margin-bottom: 10px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; }
        .body { padding: 28px 32px; }
        .check-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
        }
        .check-icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }
        .check-icon svg { width: 12px; height: 12px; color: #ffffff; }
        .info-box {
            margin-top: 24px;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 16px;
        }
        .info-box p {
            font-size: 13px;
            color: #166534;
            line-height: 1.6;
        }
        .info-box strong { font-weight: 600; }
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
                <div class="header-icon">✅</div>
                <h1>Envío de correos funcionando</h1>
                <p>Este es un correo de prueba enviado a <strong>{{ $emailDestino }}</strong></p>
            </div>

            <div class="body">
                <ul class="check-list">
                    <li>
                        <div class="check-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>El servidor de correo está configurado correctamente.</span>
                    </li>
                    <li>
                        <div class="check-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>El envío de correos desde <strong>Flujo Caja</strong> está operativo.</span>
                    </li>
                    <li>
                        <div class="check-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>La plantilla de correos se renderiza correctamente.</span>
                    </li>
                </ul>

                <div class="info-box">
                    <p>
                        💡 <strong>Mensaje de prueba</strong><br>
                        Si estás leyendo este correo, significa que el sistema de envíos de
                        <strong>Flujo Caja</strong> está funcionando correctamente.
                        Podés ignorar este mensaje o eliminarlo.
                    </p>
                </div>
            </div>

            <div class="footer">
                <p>
                    Este correo fue generado automáticamente por <strong>Flujo Caja</strong>.<br>
                    Enviado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }} hrs.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
