<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudDemoLandingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{
     *     nombreIglesia: string,
     *     nombreContacto: string,
     *     email: string,
     *     whatsapp: string,
     *     pais: string,
     *     tamanoCongregacion: string,
     *     moduloInteres: string,
     *     mensaje: string,
     *     whatsappUrl: string
     * }  $solicitud
     */
    public function __construct(public array $solicitud) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->solicitud['email'], $this->solicitud['nombreContacto']),
            ],
            subject: 'Nueva solicitud de información REDIL Cloud — '.$this->solicitud['nombreIglesia'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.solicitud-demo-landing',
        );
    }
}
