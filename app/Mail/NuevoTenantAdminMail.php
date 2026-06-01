<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoTenantAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Registro de Iglesia en REDIL Cloud: ' . $this->tenant->church_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.nuevo-tenant-admin',
        );
    }
}
