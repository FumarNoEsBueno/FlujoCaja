<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductoImportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{fila: int, datos: string, errores: string[]}>  $errores
     */
    public function __construct(
        public readonly string $nombreArchivo,
        public readonly int $totalFilas,
        public readonly int $importados,
        public readonly array $errores,
    ) {}

    public function envelope(): Envelope
    {
        $tieneErrores = count($this->errores) > 0;

        return new Envelope(
            subject: $tieneErrores
                ? '⚠️ Importación de productos — Reporte de errores'
                : '✅ Importación de productos — Completada exitosamente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.producto-import',
        );
    }
}
