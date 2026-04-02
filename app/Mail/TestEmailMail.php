<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TestEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $emailDestino,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Prueba de envío de correos — Flujo Caja',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test-email',
        );
    }
}
