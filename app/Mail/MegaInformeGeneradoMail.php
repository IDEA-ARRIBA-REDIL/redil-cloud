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
            subject: 'Informe Generado: '.$this->informe->nombre,
        );
    }

    public function content(): Content
    {
        $mailData = new \stdClass;
        $mailData->subject = 'Informe Generado: '.$this->informe->nombre;
        $mailData->eyebrow = 'INFORMES Y REPORTES · REDIL CLOUD';
        $mailData->titulo = '¡Tu informe está listo!';

        $duracion = $this->informeEnCola->tiempo_ejecucion_segundos ?? 0;
        $duracionTexto = $duracion > 60
            ? floor($duracion / 60).' min '.($duracion % 60).' seg'
            : $duracion.' seg';

        $fechaSolicitud = $this->informeEnCola->created_at
            ? $this->informeEnCola->created_at->format('d/m/Y h:i A')
            : now()->format('d/m/Y h:i A');

        $mailData->mensaje = '<p>Te informamos que tu informe solicitado ha sido procesado exitosamente y adjunto a este correo encontrarás el archivo Excel correspondiente.</p>'
            .'<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#F8F8F6;border:1px solid #EBEBEB;border-radius:8px;padding:16px;margin:16px 0 20px 0;">'
            .'  <tr>'
            .'    <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;line-height:1.7;">'
            .'      <strong>Informe:</strong> '.e($this->informe->nombre).'<br>'
            .'      <strong>Fecha de solicitud:</strong> '.$fechaSolicitud.'<br>'
            .'      <strong>Tiempo de generación:</strong> '.$duracionTexto.'<br>'
            .'      <strong>Archivo adjunto:</strong> '.e($this->informeEnCola->nombre_archivo).'.xlsx'
            .'    </td>'
            .'  </tr>'
            .'</table>'
            .'<p>También puedes ingresar al sistema en cualquier momento para descargar el archivo directamente desde la lista de informes generados.</p>';

        $mailData->actionUrl = url('/informes-personalizados/mega-informe/'.$this->informe->id);
        $mailData->actionText = 'Ver informe en el sistema →';

        $iglesia = Iglesia::first();

        return new Content(
            view: 'emails.default-mail',
            with: [
                'mailData' => $mailData,
                'configuracion' => Configuracion::first(),
                'iglesia' => $iglesia,
                'iglesiaObj' => $iglesia,
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
                    ->as($this->informeEnCola->nombre_archivo.'.xlsx')
                    ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ];
        }

        return [];
    }
}
