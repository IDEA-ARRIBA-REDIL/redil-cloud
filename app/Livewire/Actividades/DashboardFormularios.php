<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use App\Models\Actividad;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\Iglesia;
use App\Models\ActividadCarritoCompra;
use App\Models\RespuestaElementoFormulario;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\ActividadCampoAdicionalCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\FormularioRespuestasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail; // Necesario para enviar correos
use App\Mail\DefaultMail;           // El Mailable genérico que ya tienes
use App\Mail\RecordatorioFormularioMail;
use Barryvdh\DomPDF\Facade\Pdf;       // Para generar el PDF
use stdClass;


class DashboardFormularios extends Component
{
    public Actividad $actividad;
    // --- NUEVA PROPIEDAD ---
    // Almacena la cantidad de invitados que el administrador aprueba para cada inscripción.
    // Se vincula con el input de la vista usando wire:model.
    public $cantidadInvitadosAprobados = [];

    public function render()
    {

        $compradores = Compra::where('actividad_id', $this->actividad->id)->get();

        $elementosFormulario = $this->actividad->elementos()
        ->where('tipo_elemento_id', '!=', 1)
        ->orderBy('orden', 'asc')
        ->get();


        $comprasIds = $compradores->pluck('id')->toArray();
        $todasLasRespuestas = RespuestaElementoFormulario::whereIn(
            'compra_id',
            $comprasIds
        )
        ->with('elemento.tipoElemento')
        ->get();

        $mapaRespuestas = [];
        $mapaInscripciones = [];
        $todasLasInscripciones = Inscripcion::whereIn('compra_id', $comprasIds)->with('categoriaActividad')->get();

        // --- INICIO DEL CAMBIO 1: PRECARGAR LOS VALORES GUARDADOS ---
        // Se itera sobre las inscripciones para llenar el array que usa la vista.
        // Esto asegura que si ya hay un valor guardado, el input lo mostrará.
        foreach ($todasLasInscripciones as $inscripcion) {
            // Solo se asigna si no ha sido seteado por el usuario en la vista aún.
            if (!isset($this->cantidadInvitadosAprobados[$inscripcion->id])) {
                $this->cantidadInvitadosAprobados[$inscripcion->id] = $inscripcion->limite_invitados ?? 0;
            }
        }
        // --- FIN DEL CAMBIO 1 ---

        foreach ($todasLasRespuestas as $respuesta) {
            if ($respuesta->compra_id) {
                $mapaRespuestas[$respuesta->compra_id][$respuesta->elemento_formulario_actividad_id] = $respuesta;
            }
        }
        foreach ($todasLasInscripciones as $inscripcion) {
            $mapaInscripciones[$inscripcion->compra_id] = $inscripcion;
        }

        // --- INICIO DE LA NUEVA LÓGICA ---
        // Se crean los mapas existentes de respuestas e inscripciones principales
        $mapaRespuestas = [];
        $mapaInscripciones = [];

        // Separamos las inscripciones principales (con user_id) de las de invitados
        $inscripcionesPrincipales = $todasLasInscripciones->whereNotNull('compra_id');
        $inscripcionesInvitados = $todasLasInscripciones->whereNotNull('inscripcion_asociada');

        foreach ($todasLasRespuestas as $respuesta) {
            if ($respuesta->compra_id) {
                $mapaRespuestas[$respuesta->compra_id][$respuesta->elemento_formulario_actividad_id] = $respuesta;
            }
        }
        foreach ($inscripcionesPrincipales as $inscripcion) {
            $mapaInscripciones[$inscripcion->compra_id] = $inscripcion;
        }

        // Se crea un nuevo mapa para los invitados, agrupados por el ID de la inscripción principal.
        $mapaInvitados = $inscripcionesInvitados->groupBy('inscripcion_asociada');
        // --- FIN DE LA NUEVA LÓGICA ---

        // 7. Retornar la vista (sin cambios)
        return view(
            'livewire.actividades.dashboard-formularios',
            [
                'compradores' => $compradores,
                'elementosFormulario' => $elementosFormulario,
                'mapaRespuestas' => $mapaRespuestas,
                'mapaInscripciones' => $mapaInscripciones,
                'mapaInvitados' => $mapaInvitados, // Se pasa el nuevo mapa a la vista
                'todasLasRespuestas' => $todasLasRespuestas
            ]
        );
    }

    /**
     * Envía una notificación por correo a todos los participantes que NO han respondido el formulario.
     */
    public function notificarPendientes()
    {
        // 1. Validar que la actividad tenga elementos de formulario
        $preguntasObligatorias = $this->actividad->elementos()->where('tipo_elemento_id', '!=', 1)->count();
        if ($preguntasObligatorias == 0) {
            $this->dispatch('msn', [
                'msnTitulo' => 'Sin formulario',
                'msnTexto'  => 'Esta actividad no tiene preguntas configuradas para completar.',
                'msnIcono'  => 'warning'
            ]);
            return;
        }

        // 2. Obtener directamente las compras que NO tienen ninguna respuesta asociada
        $comprasPendientes = Compra::where('actividad_id', $this->actividad->id)
            ->whereDoesntHave('respuestas')
            ->with('user')
            ->get();

        if ($comprasPendientes->isEmpty()) {
            $this->dispatch('msn', [
                'msnTitulo' => 'Todo al día',
                'msnTexto'  => 'Todos los participantes ya han completado el formulario o no hay registros.',
                'msnIcono'  => 'info'
            ]);
            return;
        }

        $conteo = 0;
        $iglesia = Iglesia::find(1);

        foreach ($comprasPendientes as $compra) {
            // Intentamos obtener el email: 1. De la compra, 2. Del usuario logueado
            $emailDestino = strtolower($compra->email_comprador ?: $compra->user?->email);
            
            // Intentamos obtener el nombre: 1. De la compra, 2. Del usuario, 3. Genérico
            $nombreDestino = $compra->nombre_completo_comprador ?: ($compra->user ? $compra->user->nombre(3) : 'Participante');
            
            if (!$emailDestino) {
                Log::warning("No se pudo enviar recordatorio para Compra ID {$compra->id} porque no tiene email.");
                continue;
            }

            $mailData = new stdClass();
            $mailData->subject = 'COMPLETA LAS PREGUNTAS DE TU FORMULARIO';
            $mailData->nombre = $nombreDestino;
            $mailData->saludo = 'si';
            
            $urlFormulario = route('carrito.formulario', ['compra' => $compra->id, 'actividad' => $this->actividad->id]);
            
            $mensaje = "<p>Hemos notado que tu inscripción para la actividad <strong>{$this->actividad->nombre}</strong> aún no cuenta con las respuestas del formulario obligatorio.</p>";
            $mensaje .= "<p style='color: #d9534f; font-weight: bold;'>Es indispensable completar estos campos para validar tu participación. De lo contrario, tu inscripción podría ser anulada en las próximas horas.</p>";
            
            $mensaje .= "<div style='text-align: center; margin-top: 30px; margin-bottom: 30px;'>
                            <a href='{$urlFormulario}' style='background-color: #3b71fe; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 16px;'>
                                COMPLETAR MI FORMULARIO AHORA
                            </a>
                        </div>";
            
            $mensaje .= "<p>Si ya enviaste tus respuestas o crees que esto es un error, por favor haz caso omiso a este mensaje o contáctanos.</p>";
            
            $mailData->mensaje = $mensaje;

            try {
                Mail::to($emailDestino)->queue(new RecordatorioFormularioMail($mailData, $this->actividad));
                $conteo++;
            } catch (\Exception $e) {
                Log::error("Error enviando recordatorio a {$emailDestino}: " . $e->getMessage());
            }
        }

        $this->dispatch('msn', [
            'msnTitulo' => 'Notificaciones enviadas',
            'msnTexto'  => "Se han enviado {$conteo} correos de recordatorio de forma exitosa.",
            'msnIcono'  => 'success'
        ]);
    }

    /**
     * Elimina totalmente una compra y todos sus registros asociados.
     */
    public function eliminarCompra(int $compraId)
    {
        DB::beginTransaction();
        try {
            $compra = Compra::with('carritos.categoria')->findOrFail($compraId);

            // 1. Revertir aforo ocupado basándonos en los ítems del carrito
            // Esto es lo más preciso ya que el carrito guarda la cantidad total (principal + invitados)
            foreach ($compra->carritos as $item) {
                if ($item->categoria) {
                    $item->categoria->decrement('aforo_ocupado', $item->cantidad);
                }
            }

            // 2. Eliminar dependencias en orden
            Pago::where('compra_id', $compraId)->delete();
            ActividadCarritoCompra::where('compra_id', $compraId)->delete();
            RespuestaElementoFormulario::where('compra_id', $compraId)->delete();
            ActividadCampoAdicionalCompra::where('compra_id', $compraId)->delete();
            
            // 3. Eliminar inscripciones y finalmente la compra
            $compra->inscripciones()->delete();
            $compra->delete();

            DB::commit();

            $this->dispatch('msn', [
                'msnTitulo' => 'Registro eliminado',
                'msnTexto'  => 'La compra y sus registros han sido eliminados. Se han liberado los cupos en la categoría.',
                'msnIcono'  => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando compra desde dashboard: ' . $e->getMessage());
            $this->dispatch('msn', [
                'msnTitulo' => 'Error',
                'msnTexto'  => 'Ocurrió un error al intentar eliminar el registro: ' . $e->getMessage(),
                'msnIcono'  => 'error'
            ]);
        }
    }
    /**
     * MÉTODO RECONSTRUIDO:
     * Ahora guarda el límite de invitados y la lógica está ordenada correctamente.
     */
    public function aprobarInscripcion(int $inscripcionId)
    {

        DB::beginTransaction();
        try {
            // 1. Obtener los datos necesarios.
            $inscripcion = Inscripcion::with('categoriaActividad', 'user')->findOrFail($inscripcionId);
            $cantidadInvitados = (int)($this->cantidadInvitadosAprobados[$inscripcionId] ?? 0);
            $totalCuposADescontar = 1 + $cantidadInvitados; // 1 (principal) + N (invitados)

            // 1. Obtener los datos necesarios para la validación.
            $inscripcion = Inscripcion::with('categoriaActividad')->findOrFail($inscripcionId);
            $categoria = $inscripcion->categoriaActividad;
            $cantidadInvitadosAprobados = (int)($this->cantidadInvitadosAprobados[$inscripcionId] ?? 0);

            // ===================== INICIO DEL BLOQUE DE VALIDACIÓN =====================

            // VALIDACIÓN 1: El número de invitados aprobados no puede superar el límite de la categoría.
            if (isset($categoria->limite_invitados) && $cantidadInvitadosAprobados > $categoria->limite_invitados) {
                $this->dispatch('msn', [
                    'msnTitulo' => 'Límite de Invitados Superado',
                    'msnTexto'  => "Error: Se están aprobando <strong>{$cantidadInvitadosAprobados}</strong> invitados, pero el límite para la categoría '{$categoria->nombre}' es de <strong>{$categoria->limite_invitados}</strong>.",
                    'msnIcono'  => 'error'
                ]);
                return; // Detenemos la ejecución
            }

            // VALIDACIÓN 2: El número total de personas (principal + invitados) no puede superar el aforo disponible.
            $totalCuposRequeridos = 1 + $cantidadInvitadosAprobados;
            $aforoDisponible = $categoria->aforo - $categoria->aforo_ocupado;
            if ($totalCuposRequeridos > $aforoDisponible) {
                $this->dispatch('msn', [
                    'msnTitulo' => 'Aforo insuficiente',
                    'msnTexto'  => "Error: Se requieren <strong>{$totalCuposRequeridos}</strong> cupos (1 principal + {$cantidadInvitadosAprobados} invitados), pero solo hay <strong>{$aforoDisponible}</strong> cupos disponibles en la categoría.",
                    'msnIcono'  => 'error'
                ]);
                return; // Detenemos la ejecución
            }
            // ====================== FIN DEL BLOQUE DE VALIDACIÓN ======================


            // 2. Verificar el aforo ANTES de hacer cualquier cambio.
            $categoria = $inscripcion->categoriaActividad;
            $aforoDisponible = $categoria->aforo - $categoria->aforo_ocupado;
            if ($totalCuposADescontar > $aforoDisponible) {
                throw new \Exception("No hay suficientes cupos. Se requieren {$totalCuposADescontar} pero solo hay {$aforoDisponible} disponibles.");
            }

            // 3. Si hay cupos, actualizamos la base de datos.
            // Se descuenta el aforo.
            $categoria->increment('aforo_ocupado', $totalCuposADescontar);

            // Se actualiza la inscripción principal con el estado y el límite de invitados.
            $inscripcion->update([
                'estado' => 3, // 3 = Finalizada
                'limite_invitados' => $cantidadInvitados,
            ]);

            // 4. Confirmamos la transacción.
            DB::commit();

            // 5. Enviamos el correo de notificación.
            // (La lógica de envío de correo se mantiene igual)
            $this->enviarCorreoDeAprobacion($inscripcion, $cantidadInvitados);

            // 6. Enviamos el SweetAlert de éxito.
            // Livewire se encargará de refrescar la vista automáticamente después de esto.
            $this->dispatch('msn', [
                'msnTitulo' => '¡Éxito!',
                'msnTexto'  => 'La inscripción ha sido aprobada y el correo de notificación se está enviando.',
                'msnIcono'  => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aprobar inscripción: ' . $e->getMessage());
            $this->dispatch('msn', [
                'msnTitulo' => 'Error',
                'msnTexto'  => 'No se pudo aprobar la inscripción: ' . $e->getMessage(),
                'msnIcono'  => 'error'
            ]);
        }
    }

    // --- HE MOVIDO LA LÓGICA DE CORREO A UN MÉTODO PRIVADO PARA MÁS ORDEN ---
    private function enviarCorreoDeAprobacion(Inscripcion $inscripcion, int $cantidadInvitados)
    {
        // Se obtiene la información de la iglesia (sin cambios).


        $iglesia = Iglesia::find(1);
        try {
            $usuarioPrincipal = $inscripcion->user;
            if (!$usuarioPrincipal) return;

            $mailData = new stdClass();
            $mailData->subject = '¡Tu inscripción para ' . $this->actividad->nombre . ' ha sido aprobada!';
            $mailData->saludo = 'si';
            $mailData->nombre = $usuarioPrincipal->nombre(3);

            $mensaje = "<p>Tu inscripción ha sido aprobada con éxito.</p>";
            if ($cantidadInvitados > 0) {
                $mensaje .= "<p>Además, se han confirmado <strong>{$cantidadInvitados} cupos para tus invitados</strong>.</p>";
            }

            // --- INICIO DE LA CORRECCIÓN ---
            // 1. Se construye la URL completa y válida.
            $urlGestion = "https://" . $iglesia->url_subdominio . "/actividades/" . $inscripcion->id . "/gestionar-inscripciones";

            // 2. Se inserta la URL correcta en el mensaje del correo.
            $mensaje .= "<p>Adjunto encontrarás tu ticket personal con el código QR para el registro. Podrás gestionar los datos de tus invitados y descargar sus tickets desde el siguiente enlace: <a href='" . $urlGestion . "'>click aquí</a>.</p>";
            // --- FIN DE LA CORRECCIÓN ---

            $mailData->mensaje = $mensaje;

            $pdfData = $this->_generarPdfParaInscripcion($inscripcion);
            $pdfFilename = 'Ticket-Inscripcion-' . $inscripcion->id . '.pdf';

            Mail::to($usuarioPrincipal->email)->send(new DefaultMail($mailData, $pdfData, $pdfFilename));
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de aprobación para Inscripción ID ' . $inscripcion->id . ': ' . $e->getMessage());
            $this->dispatch('msn', [
                'msnTitulo' => 'Aprobado con advertencia',
                'msnTexto'  => 'La inscripción fue aprobada, pero ocurrió un error al enviar el correo de notificación.',
                'msnIcono'  => 'warning'
            ]);
        }
    }

    /**
     * NUEVO MÉTODO: Desaprueba una inscripción y devuelve los cupos.
     */
    public function desaprobarInscripcion(int $inscripcionId)
    {
        DB::beginTransaction();
        try {
            $inscripcion = Inscripcion::with('categoriaActividad')->findOrFail($inscripcionId);

            // Calculamos cuántos cupos vamos a devolver al aforo.
            $cuposADevolver = 1 + $inscripcion->limite_invitados;

            // Devolvemos los cupos.
            $inscripcion->categoriaActividad->decrement('aforo_ocupado', $cuposADevolver);

            // Revertimos la inscripción a estado 'Iniciada' y reseteamos los invitados.
            $inscripcion->update([
                'estado' => 1, // 1 = Iniciada
                'limite_invitados' => 0,
            ]);

            DB::commit();

            // Reseteamos el valor en el array del componente para que el input se actualice a 0.
            $this->cantidadInvitadosAprobados[$inscripcionId] = 0;

            $this->dispatch('msn', [
                'msnTitulo' => 'Acción completada',
                'msnTexto'  => 'La aprobación de la inscripción ha sido revertida.',
                'msnIcono'  => 'info'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desaprobar inscripción: ' . $e->getMessage());
            $this->dispatch('msn', ['msnTitulo' => 'Error', 'msnTexto' => 'No se pudo revertir la aprobación.' . $e->getMessage(), 'msnIcono' => 'error']);
        }
    }

    /**
     * HELPER PRIVADO: Genera el contenido binario de un PDF para un ticket de inscripción.
     */
    private function _generarPdfParaInscripcion(Inscripcion $inscripcion): string
    {
        $nombreAsistente = $inscripcion->user?->nombre(3) ?? $inscripcion->nombre_inscrito;



        // Preparamos los datos que irán dentro del código QR.
        $dataQr = json_encode([
            'id' => $inscripcion->id,
            'nombre' => $nombreAsistente,
            'tipo' => 'reserva_principal_aprobada' // La etiqueta que solicitaste
        ]);

        // Renderizamos la vista Blade del ticket en un objeto PDF.
        // Asegúrate de tener una vista en: resources/views/pdf/ticket-inscripcion.blade.php
        $pdf = PDF::loadView('contenido.paginas.actividades.inscripcion-ticket', [
            'inscripcion' => $inscripcion,
            'datosParaQr' => $dataQr,
            'actividad' => $this->actividad
        ]);

        // Devolvemos el contenido del PDF como un string.
        return $pdf->output();
    }

    /**
     * Helper para obtener el valor legible de una respuesta.
     * AJUSTADO SEGÚN TU ARCHIVO SEEDER.
     *
     * @param mixed $respuesta Objeto del modelo RespuestaFormularioElementoCompra
     * @return string
     */
    public function getValorRespuesta($respuesta): string
    {
        $configuracion = Configuracion::find(1);
        if (!$respuesta || !$respuesta->elemento) {
            return '<span class="text-muted fst-italic">Sin respuesta</span>';
        }

        // Usamos la 'clase' del seeder para decidir cómo mostrar la respuesta
        switch ($respuesta->elemento->tipoElemento->clase) {
            case 'corta': // ID: 2
                return e($respuesta->respuesta_texto_corto);

            case 'larga': // ID: 3
                return nl2br(e($respuesta->respuesta_texto_largo));

            case 'si_no': // ID: 4
                // Suponiendo que las opciones son 1 para 'Sí' y 2 para 'No'
                // O podrías buscar el texto de la opción si tienes la relación.
                return $respuesta->respuesta_si_no == 1 ? 'Sí' : 'No';

            case 'unica_respuesta': // ID: 5
                // Idealmente aquí se buscaría el texto de la opción. Por ahora, mostramos el ID.
                return 'ID de opción seleccionada: ' . e($respuesta->respuesta_unica);

            case 'multiple_respuesta': // ID: 6
                // Idealmente aquí se buscarían los textos de las opciones.
                return 'IDs de opciones: ' . e($respuesta->respuesta_multiple);

            case 'fecha': // ID: 7
                return e($respuesta->respuesta_fecha);

            case 'numero': // ID: 8
                return e($respuesta->respuesta_numero);

            case 'moneda': // ID: 9
                return '$' . number_format($respuesta->respuesta_moneda ?? 0, 2);

            case 'archivo': // ID: 10
                return $respuesta->url_archivo
                    ? '<a href="' . asset('storage/' . $configuracion->ruta_almacenamiento . '/archivos/actividades/' . $respuesta->url_archivo) . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="mdi mdi-download me-1"></i> Descargar Archivo</a>'
                    : '<span class="text-muted fst-italic">No se subió archivo</span>';

            case 'imagen': // ID: 11
                return $respuesta->url_foto
                    ? '<a href="' . asset('storage/' . $respuesta->url_foto) . '" target="_blank"><img src="' . asset('storage/' . $respuesta->url_foto) . '" class="img-fluid rounded" style="max-height: 100px; cursor: pointer;" alt="Imagen respuesta"></a>'
                    : '<span class="text-muted fst-italic">No se subió imagen</span>';

            default:
                return '<span class="text-muted fst-italic">Tipo de dato no reconocido</span>';
        }
    }

    public function exportarExcel()
    {
        // Creamos un nombre de archivo amigable
        $fileName = 'respuestas-' . Str::slug($this->actividad->nombre) . '.xlsx';

        // Usamos el Facade de Excel para descargar el archivo, pasándole nuestra clase de exportación.
        return Excel::download(new FormularioRespuestasExport($this->actividad), $fileName);
    }
}
