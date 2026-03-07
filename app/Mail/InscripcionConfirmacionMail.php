<?php

namespace App\Mail;

use App\Models\Inscripcion;
use App\Models\Actividad;
use App\Models\Iglesia; // <-- AÑADIR: Necesario para los datos en el PDF.
use App\Models\Configuracion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment; // <-- 1. Importa la clase Attachment
use Barryvdh\DomPDF\Facade\Pdf;           // <-- 2. Importa la clase PDF
use Illuminate\Support\Facades\Storage; // <-- AÑADIR: Para construir la URL del banner.
use stdClass; // <-- AÑADIR: Para crear el objeto $mailData.

class InscripcionConfirmacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public Inscripcion $inscripcion;
    public Actividad $actividad;
    public $mailData;
    public $iglesia;
    public $version;

    /**
     * MÉTODO CONSTRUCTOR MODIFICADO
     * Ahora no solo recibe los datos, sino que construye todo lo necesario para la plantilla.
     */
    public function __construct(Inscripcion $inscripcion, Actividad $actividad)
    {
        $this->inscripcion = $inscripcion;
        $this->actividad = $actividad;

        // 1. Cargamos los modelos de configuración e iglesia
        $configuracion = Configuracion::find(1);
        $this->iglesia = Iglesia::find(1);
        $this->version = $configuracion->version;

        // 2. Creamos y poblamos el objeto $mailData
        $this->mailData = new stdClass();

        // Obtenemos el nombre del participante de forma segura
        $this->mailData->nombre = $inscripcion->user?->nombre(3) ?? $inscripcion->compra->nombre_completo_comprador;
        $this->mailData->saludo = "si"; // Puedes cambiar esto a "no" si en algún caso no quieres el saludo.

        // Construimos el mensaje principal en formato HTML
        $this->mailData->mensaje = "<p> Nos alegra que vayas a ser parte de nuestro <strong>" . $actividad->nombre . "</strong></p>"
            . "<p>Adjunto encontrarás el código QR que debes presentar previo a tu ingreso..</p>";


        // Obtenemos la URL completa del banner de la actividad, si existe.

        $this->mailData->banner = $actividad->banner ? Storage::url($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre) : Storage::url($configuracion->ruta_almacenamiento . '/img/email/bannercorreo.png');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Inscripción: ' . $this->actividad->nombre,
        );
    }

    /**
     * MÉTODO EDITADO:
     * Ahora el contenido del correo apunta a la nueva vista de mensaje simple.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inscripciones',
        );
    }

    /**
     * MÉTODO NUEVO/EDITADO:
     * Aquí es donde generamos y adjuntamos el PDF.
     */
    public function attachments(): array
    {
        // 1. Cargamos la vista del TICKET (la que tiene el diseño y el QR)
        // y la convertimos en un objeto PDF en memoria.
        $pdf = Pdf::loadView('contenido.paginas.actividades.inscripcion-ticket', [
            'inscripcion' => $this->inscripcion,
            'actividad' => $this->actividad
        ]);

        // 2. Adjuntamos el PDF generado al correo electrónico.
        return [
            Attachment::fromData(fn() => $pdf->output(), 'Ticket-Inscripcion-' . $this->inscripcion->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    /**
     * --- MÉTODO AUXILIAR AÑADIDO ---
     * Encapsula la lógica de generación del PDF para mantener el código más limpio.
     * Carga los datos adicionales que la vista del PDF necesita.
     *
     * @param Inscripcion $inscripcion
     * @param Actividad $actividad
     * @return string
     */
    private function _generarPdfTicket(Inscripcion $inscripcion, Actividad $actividad): string
    {
        // Cargamos los modelos necesarios que la vista 'inscripcion-ticket' utiliza.
        $iglesia = Iglesia::find(1);
        $configuracion = Configuracion::find(1); // Necesario para la ruta del logo.

        // Generamos el PDF en memoria.
        $pdf = Pdf::loadView('contenido.paginas.actividades.inscripcion-ticket', [
            'inscripcion' => $inscripcion,
            'actividad' => $actividad,
            'iglesia' => $iglesia,
            'configuracion' => $configuracion, // Pasamos la configuración a la vista.
        ]);

        // Devolvemos el contenido binario del PDF.
        return $pdf->output();
    }
}
