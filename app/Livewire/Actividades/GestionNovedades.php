<?php

namespace App\Livewire\Actividades;

use App\Exports\NovedadesExport;
use App\Mail\DefaultMail;
use App\Models\Actividad;
use App\Models\Configuracion;
use App\Models\Iglesia;
use App\Models\NovedadActividad;
use App\Models\TipoNovedad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class GestionNovedades extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url(history: true)]
    public string $filtroEstado = '';

    #[Url(history: true)]
    public string $filtroActividadId = '';

    #[Url(history: true)]
    public string $filtroTipoNovedadId = '';

    #[Url(history: true)]
    public string $filtroFechaInicio = '';

    #[Url(history: true)]
    public string $filtroFechaFin = '';

    #[Url(history: true)]
    public string $busqueda = '';

    // Propiedades para modal de Detalle y Respuesta
    public ?int $novedadSeleccionadaId = null;

    public ?NovedadActividad $novedadSeleccionada = null;

    public string $mensajeRespuesta = '';

    public string $nuevoEstado = NovedadActividad::ESTADO_FINALIZADO;

    // Propiedades para modal de gestión de Tipos de Novedad
    public string $nuevoTipoNombre = '';

    public string $nuevoTipoDescripcion = '';

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroActividadId(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipoNovedadId(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaInicio(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaFin(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'filtroEstado',
            'filtroActividadId',
            'filtroTipoNovedadId',
            'filtroFechaInicio',
            'filtroFechaFin',
            'busqueda',
        ]);
        $this->resetPage();
    }

    /**
     * Abre el modal de detalle / respuesta para una novedad específica.
     */
    public function verNovedad(int $id): void
    {
        $this->novedadSeleccionadaId = $id;
        $this->novedadSeleccionada = NovedadActividad::with(['actividad.tipo', 'tipoNovedad', 'materia', 'respondidoPor'])->find($id);

        if ($this->novedadSeleccionada) {
            $this->mensajeRespuesta = $this->novedadSeleccionada->respuesta ?? '';
            $this->nuevoEstado = $this->novedadSeleccionada->estado === NovedadActividad::ESTADO_NO_REVISADO
                ? NovedadActividad::ESTADO_INICIADO
                : $this->novedadSeleccionada->estado;

            $this->dispatch('abrirModal', nombreModal: 'modalDetalleNovedad');
        }
    }

    public function cerrarModalDetalle(): void
    {
        $this->reset(['novedadSeleccionadaId', 'novedadSeleccionada', 'mensajeRespuesta']);
        $this->dispatch('cerrarModal', nombreModal: 'modalDetalleNovedad');
    }

    /**
     * Cambia rápidamente el estado de una novedad.
     */
    public function cambiarEstado(int $novedadId, string $estado): void
    {
        if (! Auth::user()->can('actividades.gestionar_novedades')) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Acceso denegado', msnTexto: 'No tienes permisos para gestionar novedades.');

            return;
        }

        $novedad = NovedadActividad::find($novedadId);
        if ($novedad) {
            $novedad->estado = $estado;
            $novedad->save();

            $this->dispatch('msn', msnIcono: 'success', msnTitulo: 'Estado actualizado', msnTexto: "La novedad #{$novedad->id} ahora está en estado {$novedad->estado_nombre}.");
        }
    }

    /**
     * Envía la respuesta redactada al email del usuario y actualiza la novedad.
     */
    public function enviarRespuesta(): void
    {
        if (! Auth::user()->can('actividades.gestionar_novedades')) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Acceso denegado', msnTexto: 'No tienes permisos para responder novedades.');

            return;
        }

        $this->validate([
            'mensajeRespuesta' => 'required|string|min:5',
            'nuevoEstado' => 'required|in:no_revisado,iniciado,finalizado',
        ], [
            'mensajeRespuesta.required' => 'Debes escribir el mensaje de respuesta para el usuario.',
            'mensajeRespuesta.min' => 'El mensaje de respuesta debe tener al menos 5 caracteres.',
        ]);

        if (! $this->novedadSeleccionada) {
            return;
        }

        try {
            $this->novedadSeleccionada->respuesta = trim($this->mensajeRespuesta);
            $this->novedadSeleccionada->estado = $this->nuevoEstado;
            $this->novedadSeleccionada->respondido_por_id = Auth::id();
            $this->novedadSeleccionada->fecha_respuesta = now();
            $this->novedadSeleccionada->save();

            // Preparación del correo con DefaultMail y la vista estándar emails.default-mail
            $iglesia = Iglesia::find(1);
            $configuracion = Configuracion::find(1);

            $mailData = new stdClass;
            $mailData->subject = "Respuesta a tu novedad - {$this->novedadSeleccionada->actividad->nombre}";
            $mailData->eyebrow = 'ATENCIÓN DE NOVEDAD · '.strtoupper(now()->translatedFormat('F Y'));
            $mailData->titulo = "Hola, {$this->novedadSeleccionada->nombre}";
            $mailData->subtitulo = "Hemos revisado tu reporte sobre la actividad: {$this->novedadSeleccionada->actividad->nombre}";
            $mailData->preheader = "Respuesta a tu novedad sobre {$this->novedadSeleccionada->actividad->nombre}";

            // Cuerpo del correo con el formato del mensaje del asesor
            $mensajeHtml = '<p>Estimado(a) <strong>'.e($this->novedadSeleccionada->nombre).'</strong>,</p>';
            $mensajeHtml .= '<p>En respuesta a tu reporte con asunto: <em>"'.e($this->novedadSeleccionada->asunto).'"</em>:</p>';
            $mensajeHtml .= '<div style="background-color: #f3f4f6; border-left: 4px solid #0099d9; padding: 14px 16px; margin: 16px 0; border-radius: 4px; font-style: italic;">';
            $mensajeHtml .= nl2br(e($this->mensajeRespuesta));
            $mensajeHtml .= '</div>';
            $mensajeHtml .= '<p>Puedes ingresar nuevamente al perfil de la actividad para verificar tu inscripción o proceso:</p>';

            $mailData->mensaje = $mensajeHtml;
            $mailData->boton_texto = 'Ver Actividad';
            $mailData->boton_url = route('actividades.perfil', $this->novedadSeleccionada->actividad_id);

            Mail::to($this->novedadSeleccionada->email)->send(new DefaultMail($mailData));

            $this->cerrarModalDetalle();
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Respuesta Enviada!', msnTexto: 'Se ha guardado la respuesta y enviado el correo al usuario correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al responder novedad: '.$e->getMessage());
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Error al enviar', msnTexto: 'Ocurrió un error al enviar el correo: '.$e->getMessage());
        }
    }

    /**
     * Crea un nuevo Tipo de Novedad desde el panel.
     */
    public function crearTipoNovedad(): void
    {
        if (! Auth::user()->can('actividades.gestionar_novedades')) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Acceso denegado', msnTexto: 'No tienes permisos para administrar tipos de novedad.');

            return;
        }

        $this->validate([
            'nuevoTipoNombre' => 'required|string|max:150|unique:tipos_novedad,nombre',
            'nuevoTipoDescripcion' => 'nullable|string|max:255',
        ], [
            'nuevoTipoNombre.required' => 'El nombre del tipo de novedad es obligatorio.',
            'nuevoTipoNombre.unique' => 'Ya existe un tipo de novedad con ese nombre.',
        ]);

        TipoNovedad::create([
            'nombre' => trim($nuevoTipoNombre ?? $this->nuevoTipoNombre),
            'descripcion' => trim($this->nuevoTipoDescripcion),
            'activo' => true,
        ]);

        $this->reset(['nuevoTipoNombre', 'nuevoTipoDescripcion']);
        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Creado!', msnTexto: 'El tipo de novedad se ha registrado correctamente.');
    }

    /**
     * Activa o desactiva un tipo de novedad.
     */
    public function toggleTipoNovedad(int $tipoId): void
    {
        if (! Auth::user()->can('actividades.gestionar_novedades')) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Acceso denegado', msnTexto: 'No tienes permisos para modificar tipos de novedad.');

            return;
        }

        $tipo = TipoNovedad::find($tipoId);
        if ($tipo) {
            $tipo->activo = ! $tipo->activo;
            $tipo->save();

            $estadoTexto = $tipo->activo ? 'activado' : 'desactivado';
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: 'Actualizado', msnTexto: "El tipo de novedad ha sido {$estadoTexto}.");
        }
    }

    /**
     * Exporta el listado filtrado a Excel.
     */
    public function exportarExcel()
    {
        $nombreArchivo = 'novedades_actividades_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new NovedadesExport(
            estado: $this->filtroEstado ?: null,
            actividadId: $this->filtroActividadId ? (int) $this->filtroActividadId : null,
            tipoNovedadId: $this->filtroTipoNovedadId ? (int) $this->filtroTipoNovedadId : null,
            desde: $this->filtroFechaInicio ?: null,
            hasta: $this->filtroFechaFin ?: null,
            busqueda: $this->busqueda ?: null
        ), $nombreArchivo);
    }

    public function render()
    {
        $novedades = NovedadActividad::query()
            ->with(['actividad.tipo', 'tipoNovedad', 'materia', 'respondidoPor'])
            ->estado($this->filtroEstado ?: null)
            ->actividad($this->filtroActividadId ? (int) $this->filtroActividadId : null)
            ->tipoNovedad($this->filtroTipoNovedadId ? (int) $this->filtroTipoNovedadId : null)
            ->fecha($this->filtroFechaInicio ?: null, $this->filtroFechaFin ?: null)
            ->buscar($this->busqueda ?: null)
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Contadores para badges de estado
        $conteoNoRevisados = NovedadActividad::where('estado', NovedadActividad::ESTADO_NO_REVISADO)->count();
        $conteoIniciados = NovedadActividad::where('estado', NovedadActividad::ESTADO_INICIADO)->count();
        $conteoFinalizados = NovedadActividad::where('estado', NovedadActividad::ESTADO_FINALIZADO)->count();
        $conteoTotal = NovedadActividad::count();

        $actividades = Actividad::orderBy('id', 'desc')->take(50)->get();
        $tiposNovedad = TipoNovedad::orderBy('id')->get();

        return view('livewire.actividades.gestion-novedades', [
            'novedades' => $novedades,
            'conteoNoRevisados' => $conteoNoRevisados,
            'conteoIniciados' => $conteoIniciados,
            'conteoFinalizados' => $conteoFinalizados,
            'conteoTotal' => $conteoTotal,
            'actividades' => $actividades,
            'tiposNovedad' => $tiposNovedad,
        ]);
    }
}
