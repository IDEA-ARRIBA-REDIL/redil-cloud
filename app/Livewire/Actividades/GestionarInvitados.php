<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use App\Models\Inscripcion;
use App\Models\Iglesia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;
use App\Mail\DefaultMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use stdClass;

class GestionarInvitados extends Component
{
    // --- PROPIEDADES PÚBLICAS ---

    /** @var Inscripcion La inscripción principal a la que se asocian los invitados. */
    public Inscripcion $inscripcionPrincipal;

    /** @var \Illuminate\Database\Eloquent\Collection Colección de inscripciones de invitados ya creados. */
    public $invitados;

    /** @var int El número de cupos restantes para invitar. */
    public $cuposDisponibles = 0;

    // Propiedades para el formulario del modal (vinculadas con wire:model)
    public $nombreNuevoInvitado = '';
    public $emailNuevoInvitado = '';

    /**
     * Se ejecuta al iniciar el componente.
     * Carga la inscripción principal y los datos iniciales.
     */
    public function mount(Inscripcion $inscripcionPrincipal)
    {
        $this->inscripcionPrincipal = $inscripcionPrincipal;
        $this->cargarInvitados();
    }

    /**
     * Carga/Refresca la lista de invitados desde la BD y recalcula los cupos disponibles.
     */
    public function cargarInvitados()
    {
        // Precargamos la relación 'invitados' para eficiencia.
        $this->inscripcionPrincipal->load('invitados');
        $this->invitados = $this->inscripcionPrincipal->invitados;

        // Calculamos cuántos cupos le quedan al usuario.
        $this->cuposDisponibles = $this->inscripcionPrincipal->limite_invitados - $this->invitados->count();
    }

    /**
     * Valida y guarda la inscripción de un nuevo invitado, y luego envía su correo de notificación.
     */
    public function guardarInvitado()
    {
        // PASO 1: Validar los datos del formulario del modal.
        $this->validate([
            'nombreNuevoInvitado' => 'required|string|min:3|max:255',
            'emailNuevoInvitado' => 'required|email|max:255',
        ]);

        // PASO 2: Volver a verificar los cupos como medida de seguridad.
        if ($this->cuposDisponibles <= 0) {
            $this->dispatch('mostrarAlerta', ['titulo' => 'Límite Alcanzado', 'texto' => 'Ya has registrado el máximo de invitados permitidos.', 'icono' => 'warning']);
            return;
        }

        $inscripcionInvitado = null;

        try {
            // PASO 3: Crear el nuevo registro de inscripción para el invitado.
            $inscripcionInvitado = Inscripcion::create([
                'user_id' => null, // Es un invitado, no un usuario del sistema.
                'actividad_categoria_id' => $this->inscripcionPrincipal->actividad_categoria_id,
                'compra_id'              => $this->inscripcionPrincipal->compra_id,
                'fecha'                  => now()->toDateString(),
                'estado'                 => 3, // Se crea directamente como Aprobada/Finalizada.
                'nombre_inscrito'        => $this->nombreNuevoInvitado,
                'email'                  => $this->emailNuevoInvitado,
                'inscripcion_asociada'   => $this->inscripcionPrincipal->id,
            ]);

            // PASO 4: Refrescar la lista de invitados y limpiar el formulario.
            $this->cargarInvitados();
            $this->reset(['nombreNuevoInvitado', 'emailNuevoInvitado']);
            $this->dispatch('cerrarModalInvitado'); // Evento para que Alpine.js cierre el modal.

            // PASO 5: Si la inscripción se creó correctamente, se envía el correo.
            if ($inscripcionInvitado) {
                $this->_enviarCorreoInvitadoAprobado($inscripcionInvitado);
            }

            $this->dispatch('mostrarAlerta', ['titulo' => '¡Éxito!', 'texto' => 'El invitado ha sido registrado y notificado por correo.', 'icono' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error al guardar invitado: ' . $e->getMessage());
            $this->dispatch('mostrarAlerta', ['titulo' => 'Error', 'texto' => 'Ocurrió un error al registrar al invitado.' . $e->getMessage(), 'icono' => 'error']);
        }
    }

    /**
     * NUEVO MÉTODO:
     * Genera la imagen de un código QR para un invitado específico y la envía
     * al navegador para su descarga.
     */
    public function descargarQrInvitado(int $inscripcionId)
    {
        try {
            // 1. Buscar la inscripción del invitado de forma segura.
            $invitado = Inscripcion::where('id', $inscripcionId)
                ->where('inscripcion_asociada', $this->inscripcionPrincipal->id)
                ->firstOrFail(); // Si no se encuentra, falla con un error 404.

            // 2. Preparar los datos que irán dentro del código QR.
            $datosParaQr = json_encode([
                'id' => $invitado->id,
                'nombre' => $invitado->nombre_inscrito,
                'tipo' => 'inscripcion_invitado_aprobada'
            ]);

            // 3. Generar el contenido binario de la imagen PNG del código QR.
            $qrCodeImage = DNS2D::getBarcodePNG($datosParaQr, 'QRCODE', 10, 10); // Aumentamos el tamaño para mejor calidad

            // 4. Preparar un nombre de archivo amigable.
            $fileName = 'qr-invitado-' . Str::slug($invitado->nombre_inscrito) . '-' . $invitado->id . '.png';

            // 5. Enviar la imagen al navegador como una descarga.
            return response()->streamDownload(
                fn() => print($qrCodeImage),
                $fileName,
                ['Content-Type' => 'image/png']
            );
        } catch (\Exception $e) {
            Log::error("Error al descargar QR para invitado: " . $e->getMessage());
            // Notificar al usuario que algo salió mal.
            $this->dispatch('mostrarAlerta', [
                'titulo' => 'Error',
                'texto' => 'No se pudo generar el código QR para la descarga.',
                'icono' => 'error'
            ]);
        }
    }


    /**
     * Elimina la inscripción de un invitado.
     */
    public function eliminarInvitado(int $inscripcionId)
    {
        try {
            $invitado = Inscripcion::find($inscripcionId);
            if ($invitado && $invitado->inscripcion_asociada == $this->inscripcionPrincipal->id) {
                $invitado->delete();
                $this->cargarInvitados(); // Refrescar la lista y el contador de cupos.
                $this->dispatch('mostrarAlerta', ['titulo' => 'Eliminado', 'texto' => 'El invitado ha sido eliminado.', 'icono' => 'success']);
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar invitado: ' . $e->getMessage());
            $this->dispatch('mostrarAlerta', ['titulo' => 'Error', 'texto' => 'No se pudo eliminar al invitado.', 'icono' => 'error']);
        }
    }

    /**
     * Prepara y envía el correo de aprobación al invitado.
     */
    private function _enviarCorreoInvitadoAprobado(Inscripcion $inscripcionInvitado)
    {
        $inscripcionInvitado->load('inscripcionPrincipal.user', 'categoriaActividad');
        $participantePrincipal = $inscripcionInvitado->inscripcionPrincipal->user;

        $mailData = new stdClass();
        $mailData->subject = '¡Han confirmado tu inscripción para ' . $inscripcionInvitado->categoriaActividad->actividad->nombre . '!';
        $mailData->saludo = 'si';
        $mailData->nombre = $inscripcionInvitado->nombre_inscrito;

        $mailData->mensaje = "<p>Te informamos que <strong>" . $participantePrincipal->nombre(3) . "</strong> ha confirmado tu inscripción como su invitado a esta actividad.</p>";
        $mailData->mensaje .= "<p>Adjunto encontrarás tu ticket personal con el código QR, el cual deberás presentar para registrar tu asistencia.</p>";

        $pdfData = $this->_generarPdfParaInscripcion($inscripcionInvitado);
        $pdfFilename = 'Ticket-Invitado-' . $inscripcionInvitado->id . '.pdf';

        Mail::to($inscripcionInvitado->email)->send(new DefaultMail($mailData, $pdfData, $pdfFilename));
    }

    /**
     * Genera el contenido binario de un PDF para un ticket de inscripción.
     */
    private function _generarPdfParaInscripcion(Inscripcion $inscripcion): string
    {
        $inscripcion->load('user', 'compra', 'categoriaActividad');
        $actividad = $inscripcion->categoriaActividad->actividad;
        $iglesia = Iglesia::find(1);

        // La vista del PDF se encarga de la lógica del QR
        $pdf = PDF::loadView('contenido.paginas.actividades.inscripcion-ticket', [
            'inscripcion' => $inscripcion,
            'actividad' => $actividad,
            'iglesia' => $iglesia,
        ]);

        return $pdf->output();
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.actividades.gestionar-invitados');
    }
}
