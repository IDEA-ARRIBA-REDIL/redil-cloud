<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccesoInicialTenantMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $codigo, public string $iglesia) {}

    public function build(): static
    {
        return $this->subject('Establece tu acceso inicial a REDIL')->view('mail.acceso-inicial-tenant');
    }
}
