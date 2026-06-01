<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuotaAlertaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public int $miembros,
        public int $maxMiembros,
        public float $ratio
    ) {}

    public function envelope(): Envelope
    {
        $nivel = $this->ratio >= 1.0 ? 'CRÍTICO' : 'IMPORTANTE';

        return new Envelope(
            subject: "[{$nivel}] Alerta de límite de miembros superado - REDIL Cloud",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cuota-alerta',
        );
    }
}
