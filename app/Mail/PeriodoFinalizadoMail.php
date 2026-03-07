<?php

namespace App\Mail;

use App\Models\Periodo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PeriodoFinalizadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $periodo;

    /**
     * Create a new message instance.
     *
     * @param Periodo $periodo El periodo que se acaba de procesar.
     */
    public function __construct(Periodo $periodo)
    {
        $this->periodo = $periodo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Proceso de Finalización de Periodo Completado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Usaremos una vista de Markdown para un correo simple y elegante.
        return new Content(
            markdown: 'emails.periodo-finalizado',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
