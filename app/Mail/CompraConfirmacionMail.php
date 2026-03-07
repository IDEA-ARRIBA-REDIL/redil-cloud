<?php

namespace App\Mail;

use App\Models\Actividad;
use App\Models\Compra;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;

use App\Models\Matricula; // Importar Matricula

class CompraConfirmacionMail extends Mailable
{
    use Queueable, SerializesModels;

    // Propiedades para todos los datos que necesitamos
    public Compra $compra;
    public Pago $pago;
    public Inscripcion $inscripcion;
    public Actividad $actividad;
    public ?Matricula $matricula; // Nueva propiedad opcional

    /**
     * El constructor ahora acepta todos los objetos necesarios.
     */
    public function __construct(Compra $compra, Pago $pago, Inscripcion $inscripcion, Actividad $actividad, ?Matricula $matricula = null)
    {
        $this->compra = $compra;
        $this->pago = $pago;
        $this->inscripcion = $inscripcion;
        $this->actividad = $actividad;
        $this->matricula = $matricula;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Compra: ' . $this->actividad->nombre,
        );
    }

    /**
     * El cuerpo del correo usará una vista de mensaje simple.
     */
    public function content(): Content
    {
        // Pasamos 'compra' a la vista para poder usar sus datos en el mensaje.
        return new Content(
            view: 'emails.mensaje-confirmacion-compra',
            with: [
                'compra' => $this->compra,
            ],
        );
    }

    /**
     * El método attachments generará y adjuntará el PDF del ticket de compra.
     */
    public function attachments(): array
    {
        // Generamos el PDF usando una nueva vista específica para el ticket de compra.
        $pdf = Pdf::loadView('contenido.paginas.actividades.compra-ticket', [
            'compra' => $this->compra,
            'pago' => $this->pago,
            'inscripcion' => $this->inscripcion,
            'actividad' => $this->actividad,
            'matricula' => $this->matricula, // Pasamos la matrícula a la vista
        ]);

        return [
            Attachment::fromData(fn() => $pdf->output(), 'Ticket-Compra-' . $this->compra->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
