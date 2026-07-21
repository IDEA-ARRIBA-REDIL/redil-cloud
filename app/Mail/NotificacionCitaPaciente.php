<?php

namespace App\Mail;

use App\Models\CitaConsejeria;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionCitaPaciente extends Mailable
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
                ? 'Reagendamiento de consejería'
                : 'Confirmación de consejería',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $mailData = new \stdClass;
        $mailData->subject = $this->esReprogramacion
            ? 'Reagendamiento de consejería'
            : 'Confirmación de consejería';
        $mailData->eyebrow = 'CONSEJERÍA · CONFIRMACIÓN DE CITA';
        $mailData->titulo = $this->esReprogramacion
            ? 'Reagendamiento de Cita'
            : '¡Tu cita está confirmada!';
        $mailData->nombre = $this->cita->user?->nombre(3);

        // 1. Mensaje de introducción
        $introduccion = $this->esReprogramacion
            ? 'Nos alegra confirmarte que tu cita de consejería ha sido reagendada con éxito. Por favor, revisa los detalles a continuación.'
            : 'Nos alegra confirmarte que tu cita de consejería ha sido agendada con éxito. Por favor, revisa los detalles a continuación.';

        $mailData->mensaje = '<p>'.$introduccion.'</p>'
            .'<p style="font-size:13.5px;color:#6B7280;">Hemos adjuntado una invitación de calendario (.ics) a este correo para que puedas añadirla a tu calendario personal (Google, Outlook, etc.) con un solo clic.</p>';

        // 2. Tabla de detalles estilizada
        $fechaIso = $this->cita->fecha_hora_inicio->isoFormat('dddd, D [de] MMMM [de] YYYY');
        $horaFormat = $this->cita->fecha_hora_inicio->format('g:i A');
        $modalidad = $this->cita->medio == 1 ? 'Presencial' : 'Virtual';

        $mailData->mensaje .= '
        <h3 style="font-family:Georgia,\'Times New Roman\',serif;font-size:16px;font-weight:700;color:#040407;margin:20px 0 10px 0;">Detalles de la Cita</h3>
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;border-collapse:collapse;border:1px solid #EBEBEB;margin-bottom:20px;">
          <tbody>
            <tr>
              <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;border-bottom:1px solid #EBEBEB;background-color:#F8F8F6;width:30%;">Consejero(a)</td>
              <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;border-bottom:1px solid #EBEBEB;">'.e($this->cita->consejero?->usuario?->nombre(3)).'</td>
            </tr>
            <tr>
              <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;border-bottom:1px solid #EBEBEB;background-color:#F8F8F6;">Motivo</td>
              <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;border-bottom:1px solid #EBEBEB;">'.e($this->cita->tipoConsejeria?->nombre).'</td>
            </tr>
            <tr>
              <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;border-bottom:1px solid #EBEBEB;background-color:#F8F8F6;">Fecha</td>
              <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;border-bottom:1px solid #EBEBEB;">'.e($fechaIso).'</td>
            </tr>
            <tr>
              <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;border-bottom:1px solid #EBEBEB;background-color:#F8F8F6;">Hora</td>
              <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;border-bottom:1px solid #EBEBEB;">'.e($horaFormat).' (Hora de Colombia)</td>
            </tr>
            <tr>
              <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;background-color:#F8F8F6;">Modalidad</td>
              <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;">'.$modalidad.'</td>
            </tr>
          </tbody>
        </table>';

        // 3. Ubicación o Enlace
        if ($this->cita->medio == 1) {
            $mailData->mensaje .= '<p style="margin-top:16px;"><strong>Ubicación:</strong><br>'.e($this->cita->consejero?->direccion ?? 'Dirección no especificada. Por favor, contacta a tu consejero.').'</p>';
        } else {
            if ($this->cita->enlace_virtual) {
                $mailData->actionUrl = $this->cita->enlace_virtual;
                $mailData->actionText = 'Unirse a la reunión virtual →';
            } else {
                $mailData->mensaje .= '<p style="font-style:italic;color:#6B7280;margin-top:16px;">Enlace de la videollamada pendiente de generación.</p>';
            }
        }

        // 4. Notas de reprogramación o políticas de 24 horas
        if ($this->esReprogramacion) {
            $mailData->mensaje .= '<p style="font-size:13px;color:#B91C1C;font-weight:700;margin-top:20px;line-height:1.5;">'
                .'Nota: Si has agregado la cita anterior en tu calendario personal, te recomendamos eliminarla.'
                .'</p>';
        } else {
            $mailData->mensaje .= '<p style="font-size:13px;color:#4B5563;margin-top:20px;line-height:1.5;">'
                .'Para reprogramar o cancelar tu cita, por favor hazlo con al menos 24 horas de antelación.'
                .'</p>';
        }

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
        // ¡Aquí adjuntamos el string como un archivo .ics!
        return [
            Attachment::fromData(fn () => $this->icsContenido, 'cita_consejeria.ics')
                ->withMime('text/calendar; method=REQUEST'),
        ];
    }
}
