<?php

namespace App\Mail;

use App\Models\MateriaPeriodo; // <-- Importante: Usar el modelo correcto
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MateriaFinalizadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public MateriaPeriodo $materiaPeriodo;

    /**
     * Create a new message instance.
     */
    public function __construct(MateriaPeriodo $materiaPeriodo)
    {
        $this->materiaPeriodo = $materiaPeriodo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // El asunto ahora es más específico
        return new Envelope(
            subject: 'Proceso de Finalización de Materia Completado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Apuntamos a una nueva vista de correo que crearemos en el siguiente paso
        return new Content(
            markdown: 'emails.materia-finalizada',
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
