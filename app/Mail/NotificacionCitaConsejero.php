<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\CitaConsejeria;
use Illuminate\Mail\Mailables\Attachment;

class NotificacionCitaConsejero extends Mailable
{
    use Queueable, SerializesModels;

    public CitaConsejeria $cita;
    public string $icsContenido;
    public bool $esReprogramacion;

    /**
     * Create a new message instance.
     */
    public function __construct(CitaConsejeria $cita, string $icsContenido, bool $esReprogramacion = false)
    {
        $this->cita = $cita;
        $this->icsContenido = $icsContenido;
        $this->esReprogramacion = $esReprogramacion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->esReprogramacion 
                ? 'Reagendamiento cita de consejeria' 
                : 'Nueva cita de consejeria',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // 1. Asegúrate de crear esta vista:
        //    resources/views/emails/citas/paciente.blade.php
        return new Content(
            markdown: 'emails.consejero', // Usamos markdown
            with: [
                'cita' => $this->cita,
                'esReprogramacion' => $this->esReprogramacion,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // ¡Aquí adjuntamos el string como un archivo .ics!
        return [
            Attachment::fromData(fn () => $this->icsContenido, 'cita_consejeria.ics')
                ->withMime('text/calendar; method=REQUEST'),
        ];
    }
}
