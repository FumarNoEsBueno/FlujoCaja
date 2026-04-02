<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\TestEmailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test {email : Correo electrónico de destino}';

    protected $description = 'Envía un correo de prueba para verificar que el envío de emails funciona correctamente';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("❌ El correo proporcionado no es válido: {$email}");
            return self::FAILURE;
        }

        $this->info("📧 Enviando correo de prueba a {$email}...");

        try {
            Mail::to($email)->send(new TestEmailMail($email));

            $this->newLine();
            $this->info('✅ Correo enviado correctamente.');
            $this->info("   Revisá la bandeja de entrada (o spam) de: {$email}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error al enviar el correo.');
            $this->error('   Mensaje: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
