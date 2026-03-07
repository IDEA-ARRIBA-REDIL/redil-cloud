<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertaReportePendienteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $encargado;
    public $gruposPendientes;

    /**
     * Create a new message instance.
     *
     * @param User $encargado
     * @param array $gruposPendientes
     */
    public function __construct(User $encargado, array $gruposPendientes)
    {
        $this->encargado = $encargado;
        $this->gruposPendientes = $gruposPendientes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: Reporte de grupo pendiente',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reportes.alerta-pendiente'
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
