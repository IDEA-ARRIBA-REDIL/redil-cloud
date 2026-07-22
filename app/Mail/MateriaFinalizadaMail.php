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
            subject: 'Proceso de finalización de materia completado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $mailData = new \stdClass();
        $mailData->subject = 'Proceso de finalización de materia completado';
        $mailData->eyebrow = 'ACADÉMICO · MATERIA FINALIZADA';
        $mailData->titulo = 'Proceso de materia completado';
        
        $materiaNombre = $this->materiaPeriodo->materia?->nombre ?? 'Materia';
        $periodoNombre = $this->materiaPeriodo->periodo?->nombre ?? 'Periodo';

        $mailData->mensaje = '<p>Te informamos que el proceso de finalización para la materia:</p>'
            . '<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#F8F8F6;border:1px solid #EBEBEB;border-radius:8px;padding:16px;margin:16px 0 20px 0;">'
            . '  <tr>'
            . '    <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;line-height:1.6;">'
            . '      <strong>Materia:</strong> ' . $materiaNombre . '<br>'
            . '      <strong>Periodo Académico:</strong> ' . $periodoNombre
            . '    </td>'
            . '  </tr>'
            . '</table>'
            . '<p>ha concluido exitosamente.</p>'
            . '<p>Ya puedes consultar los resultados y el estado final de los alumnos para esta materia en el sistema.</p>';

        $mailData->actionUrl = url('/dashboard');
        $mailData->actionText = 'Consultar en el sistema →';

        return new Content(
            view: 'emails.default-mail',
            with: [
                'mailData' => $mailData,
                'iglesia' => \App\Models\Iglesia::find(1),
                'configuracion' => \App\Models\Configuracion::find(1),
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
        return [];
    }
}
