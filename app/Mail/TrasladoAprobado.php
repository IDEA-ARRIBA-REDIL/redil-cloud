<?php

namespace App\Mail;

use App\Models\TrasladoMatriculaLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrasladoAprobado extends Mailable
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
        $mailData->subject = 'Solicitud de traslado aprobada';
        $mailData->eyebrow = 'ACADÉMICO · TRASLADO DE GRUPO';
        $mailData->titulo = 'Solicitud de traslado aprobada';
        $mailData->nombre = $this->solicitud->user?->primer_nombre ?? '';

        $materiaNombre = $this->solicitud->matricula?->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre ?? 'Materia';

        $nuevoGrupo = 'Grupo';
        if ($this->solicitud->horarioDestino && $this->solicitud->horarioDestino->horarioBase) {
            $nuevoGrupo = $this->solicitud->horarioDestino->horarioBase->dia_semana.' '.$this->solicitud->horarioDestino->horarioBase->hora_inicio_formato;
        }

        $sede = 'Sede';
        if ($this->solicitud->horarioDestino && $this->solicitud->horarioDestino->horarioBase && $this->solicitud->horarioDestino->horarioBase->aula && $this->solicitud->horarioDestino->horarioBase->aula->sede) {
            $sede = $this->solicitud->horarioDestino->horarioBase->aula->sede->nombre;
        }

        $mailData->mensaje = '<p>Tu solicitud de traslado para la materia <strong>'.e($materiaNombre).'</strong> ha sido <strong>APROBADA</strong>.</p>'
            .'<h3 style="font-family:Georgia,\'Times New Roman\',serif;font-size:16px;font-weight:700;color:#040407;margin:20px 0 10px 0;">Detalles del Cambio</h3>'
            .'<table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;border-collapse:collapse;border:1px solid #EBEBEB;margin-bottom:20px;">'
            .'  <tbody>'
            .'    <tr>'
            .'      <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;border-bottom:1px solid #EBEBEB;background-color:#F8F8F6;width:30%;">Materia</td>'
            .'      <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;border-bottom:1px solid #EBEBEB;">'.e($materiaNombre).'</td>'
            .'    </tr>'
            .'    <tr>'
            .'      <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;border-bottom:1px solid #EBEBEB;background-color:#F8F8F6;">Nuevo Grupo</td>'
            .'      <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;border-bottom:1px solid #EBEBEB;">'.e($nuevoGrupo).'</td>'
            .'    </tr>'
            .'    <tr>'
            .'      <td style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#4B5563;padding:10px 14px;background-color:#F8F8F6;">Sede</td>'
            .'      <td style="font-family:Arial,sans-serif;font-size:14px;color:#374151;padding:10px 14px;">'.e($sede).'</td>'
            .'    </tr>'
            .'  </tbody>'
            .'</table>'
            .'<p>Ya puedes asistir a tu nuevo horario. Tu registro de notas y asistencia ha sido actualizado automáticamente.</p>';

        $mailData->actionUrl = url('/dashboard');
        $mailData->actionText = 'Acceder a mi Portal →';

        return $this->subject('Solicitud de traslado aprobada')
            ->view('emails.default-mail')
            ->with([
                'mailData' => $mailData,
                'iglesia' => \App\Models\Iglesia::find(1),
                'configuracion' => \App\Models\Configuracion::find(1),
            ]);
    }
}
