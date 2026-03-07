<?php

namespace App\Mail;

use App\Models\Configuracion;
use App\Models\Iglesia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RecordatorioFormularioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData, $iglesia, $version, $actividad;

    public function __construct($mailData, $actividad)
    {
        $this->mailData = $mailData;
        $this->actividad = $actividad;
        $this->iglesia = Iglesia::find(1);
        $configuracion = Configuracion::find(1);
        $this->version = $configuracion->version;

        if (!isset($this->mailData->banner)) {
            $this->mailData->banner = $actividad->banner 
                ? Storage::url($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre) 
                : Storage::url($configuracion->ruta_almacenamiento . '/img/email/bannercorreo.png');
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
        return new Content(
            view: 'emails.recordatorio-formulario',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
