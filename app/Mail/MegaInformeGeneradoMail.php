<?php

namespace App\Mail;

use App\Models\Configuracion;
use App\Models\Iglesia;
use App\Models\Informe;
use App\Models\InformeEnCola;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MegaInformeGeneradoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly InformeEnCola $informeEnCola,
        public readonly Informe $informe,
        public readonly string $filePath
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informe Generado: ' . $this->informe->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mega-informe-generado',
            with: [
                'informe' => $this->informe,
                'informeEnCola' => $this->informeEnCola,
                'configuracion' => Configuracion::first(),
                'iglesia' => Iglesia::first(),
            ]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (file_exists($this->filePath)) {
            return [
                Attachment::fromPath($this->filePath)
                    ->as($this->informeEnCola->nombre_archivo . '.xlsx')
                    ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ];
        }

        return [];
    }
}
