<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuentaActivadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant, public string $domain) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu cuenta en REDIL Cloud ha sido Activada!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cuenta-activada',
        );
    }
}
