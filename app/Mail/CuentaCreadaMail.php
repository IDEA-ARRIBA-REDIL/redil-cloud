<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;

class CuentaCreadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $domain;

    public function __construct(Tenant $tenant, $domain)
    {
        $this->tenant = $tenant;
        $this->domain = $domain;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu cuenta en REDIL Cloud está lista!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cuenta-creada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
