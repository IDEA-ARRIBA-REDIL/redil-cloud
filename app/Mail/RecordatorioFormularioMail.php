<?php

namespace App\Mail;

use App\Models\Configuracion;
use App\Models\Iglesia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioFormularioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    public $iglesia;

    public $version;

    public $actividad;

    public function __construct($mailData, $actividad)
    {
        $this->mailData = $mailData;
        $this->actividad = $actividad;
        $this->iglesia = Iglesia::find(1);
        $configuracion = Configuracion::find(1);

        if (! isset($this->mailData->banner)) {
            $this->mailData->banner = $actividad->portada_url;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData->subject,
        );
    }

    public function content(): Content
    {
        // Seteamos eyebrow por defecto si no viene en el mailData
        if (! isset($this->mailData->eyebrow)) {
            $this->mailData->eyebrow = 'RECORDATORIO · FORMULARIO PENDIENTE';
        }

        return new Content(
            view: 'emails.default-mail',
            with: [
                'mailData' => $this->mailData,
                'iglesia' => $this->iglesia ?? \App\Models\Iglesia::find(1),
                'configuracion' => \App\Models\Configuracion::find(1),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
