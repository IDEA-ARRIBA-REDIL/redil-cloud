<?php

namespace App\Mail;

use App\Models\TrasladoMatriculaLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrasladoRechazado extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TrasladoMatriculaLog $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function build()
    {
        $mailData = new \stdClass;
        $mailData->subject = 'Solicitud de traslado rechazada';
        $mailData->eyebrow = 'ACADÉMICO · TRASLADO DE GRUPO';
        $mailData->titulo = 'Solicitud de traslado rechazada';
        $mailData->nombre = $this->solicitud->user?->primer_nombre ?? '';

        $materiaNombre = $this->solicitud->matricula?->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre ?? 'Materia';

        $mailData->mensaje = '<p>Tu solicitud de traslado para la materia <strong>'.e($materiaNombre).'</strong> ha sido <strong>RECHAZADA</strong>.</p>'
            .'<p>Deberás continuar asistiendo a tu horario actual de clases.</p>';

        // Contenedor de Alerta en HTML Adicional para el motivo de rechazo
        $mailData->htmlAdicional = '
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#FDF2F2;border-left:4px solid #EF4444;border-radius:4px;padding:16px;margin-top:16px;">
          <tr>
            <td style="font-family:Arial,sans-serif;font-size:14px;color:#9B1C1C;line-height:1.6;font-style:italic;">
              <strong>Motivo del Rechazo:</strong><br>
              "'.e($this->solicitud->motivo_rechazo ?? 'No especificado por el administrador.').'"
            </td>
          </tr>
        </table>';

        $mailData->actionUrl = url('/dashboard');
        $mailData->actionText = 'Acceder a mi portal →';

        return $this->subject('Solicitud de traslado rechazada')
            ->view('emails.default-mail')
            ->with([
                'mailData' => $mailData,
                'iglesia' => \App\Models\Iglesia::find(1),
                'configuracion' => \App\Models\Configuracion::find(1),
            ]);
    }
}
