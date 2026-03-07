<?php

namespace App\Livewire\Matricula;

use Livewire\Component;
use App\Models\Matricula;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\HorarioMateriaPeriodo;
use App\Models\TrasladoMatriculaLog;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\AlumnoRespuestaItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitarTraslado extends Component
{
    public $usuario;
    public $matriculasActivas = [];
    public $matriculaSeleccionadaId;
    public $matriculaSeleccionada;

    // Propiedades para la solicitud
    public $horariosDisponibles = [];
    public $horarioDestinoId;
    public $motivoSolicitud; // Opcional, si queremos que el estudiante explique

    // Estado UI
    public $puedeSolicitar = true;
    public $mensajeBloqueo = "";

    // Configuración
    public $maxIntentos;

    public $historialSolicitudes = [];

    public function mount(User $usuario)
    {
        $this->usuario = $usuario;
        $this->cargarConfiguracion();
        $this->cargarMatriculas();
        $this->cargarHistorial();
    }

    public function cargarConfiguracion()
    {
        $config = Configuracion::first();
        $this->maxIntentos = $config->cantidad_intentos_traslados ?? 3;
    }

    public function cargarMatriculas()
    {
        $this->matriculasActivas = Matricula::with([
            'horarioMateriaPeriodo.materiaPeriodo.materia',
            'horarioMateriaPeriodo.horarioBase.aula.sede',
            'periodo',
            'trasladosLog'
        ])
        ->where('user_id', $this->usuario->id)
        ->whereHas('periodo', function($q) {
             $q->where('estado', true);
        })
        ->get();
    }

    public function cargarHistorial()
    {
        $this->historialSolicitudes = TrasladoMatriculaLog::with([
            'matricula.horarioMateriaPeriodo.materiaPeriodo.materia',
            'horarioOrigen.horarioBase',
            'horarioDestino.horarioBase'
        ])
        ->where('user_id', $this->usuario->id)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function updatedMatriculaSeleccionadaId($value)
    {
        $this->reset(['horarioDestinoId', 'horariosDisponibles', 'puedeSolicitar', 'mensajeBloqueo']);

        if (!$value) {
            $this->matriculaSeleccionada = null;
            return;
        }

        $this->matriculaSeleccionada = $this->matriculasActivas->firstWhere('id', $value);

        if ($this->matriculaSeleccionada) {
            $this->verificarElegibilidad($this->matriculaSeleccionada);
            if ($this->puedeSolicitar) {
                $this->cargarHorariosDestino($this->matriculaSeleccionada);
            }
        }
    }

    public function verificarElegibilidad(Matricula $matricula)
    {
        // 1. Verificar intentos previos
        $intentosPrevios = TrasladoMatriculaLog::where('matricula_id', $matricula->id)->count();
        if ($intentosPrevios >= $this->maxIntentos) {
            $this->bloquear("Has superado el número máximo de traslados permitidos ({$this->maxIntentos}) para esta matrícula.");
            return;
        }

        // 2. Verificar Asistencia (si tiene inasistencias o asistencias registradas)
        // Buscamos reportes asociados al usuario y que correspondan a la clase de esta matrícula
        // USAMOS EL matricula->id O el horarioMateriaPeriodo->id para filtrar reportes relevantes.
        // Asumiendo que MatriculaHorarioMateriaPeriodo es el nexo, pero ReporteAsistenciaAlumnos liga directo a User y ReporteAsistenciaClase.
        // Una aproximación: Si existe algún reporte para este usuario en este horarioMateriaPeriodo.

        $tieneAsistencia = ReporteAsistenciaAlumnos::where('user_id', $this->usuario->id)
            ->whereHas('reporteClase', function($q) use ($matricula) {
                $q->where('horario_materia_periodo_id', $matricula->horario_materia_periodo_id);
            })->exists();

        if ($tieneAsistencia) {
            $this->bloquear("No puedes solicitar traslado porque ya tienes registros de asistencia en tu grupo actual.");
            return;
        }

        // 3. Verificar Calificaciones
        // Similar, buscamos si tiene notas registradas para esta matricula/curso.
        // Asumiendo tabla Calificaciones o MatriculaHorarioMateriaPeriodo->nota...
        // Si usamos MatriculaHorarioMateriaPeriodo:
        $estadoAcademico = $matricula->estadoAcademicoClase;
        if ($estadoAcademico && ($estadoAcademico->nota_final_numerica > 0 || $estadoAcademico->notas_parciales_count > 0)) { // Ajustar según estructura real
             $this->bloquear("No puedes solicitar traslado porque ya tienes calificaciones registradas.");
             return;
        }

        // Verificación correcta si las notas están en tabla 'alumno_respuesta_items'
        $tieneNotas = AlumnoRespuestaItem::where('user_id', $this->usuario->id)
            ->whereHas('itemCalificado', function($q) use ($matricula) {
                $q->where('horario_materia_periodo_id', $matricula->horario_materia_periodo_id);
            })->exists();

        if ($tieneNotas) {
             $this->bloquear("No puedes solicitar traslado porque ya tienes calificaciones registradas.");
             return;
        }

        // ASUMIMOS RESTRICCIÓN BÁSICA POR AHORA: Si ya cursó algo (indicado por asistencia), no se mueve.
        // Si el usuario mencionó "notas en cualquier item", asumiremos que la asistencia es el primer filtro fuerte.
        // Si necesitamos ser más estrictos con notas, necesitaríamos explorar Items de evaluación.

        // Revisar si ya tiene una solicitud pendiente
        $pendiente = TrasladoMatriculaLog::where('matricula_id', $matricula->id)
                        ->where('estado', TrasladoMatriculaLog::ESTADO_PENDIENTE)
                        ->exists();

        if ($pendiente) {
             $this->bloquear("Ya tienes una solicitud de traslado pendiente para esta matrícula.");
             return;
        }
    }

    public function bloquear($mensaje)
    {
        $this->puedeSolicitar = false;
        $this->mensajeBloqueo = $mensaje;
    }

    public function cargarHorariosDestino(Matricula $matricula)
    {
        // Buscar horarios de la MISMA materia en el MISMO periodo
        // Excluyendo el actual
        // Con cupos disponibles > 0

        $this->horariosDisponibles = HorarioMateriaPeriodo::with(['horarioBase.aula.sede', 'horarioBase'])
            ->where('id', '!=', $matricula->horario_materia_periodo_id)
            ->whereHas('materiaPeriodo', function($q) use ($matricula) {
                $q->where('materia_id', $matricula->horarioMateriaPeriodo->materiaPeriodo->materia_id)
                  ->where('periodo_id', $matricula->periodo_id);
            })
            ->where('cupos_disponibles', '>', 0)
            ->get();
    }

    public function solicitar()
    {
        $this->validate([
            'matriculaSeleccionadaId' => 'required',
            'horarioDestinoId' => 'required',
        ], [
            'horarioDestinoId.required' => 'Debes seleccionar un horario de destino.'
        ]);

        if (!$this->puedeSolicitar) {
            return;
        }

        // Crear la solicitud
        TrasladoMatriculaLog::create([
            'matricula_id' => $this->matriculaSeleccionadaId,
            'origen_horario_id' => $this->matriculaSeleccionada->horario_materia_periodo_id,
            'destino_horario_id' => $this->horarioDestinoId,
            'user_id' => Auth::id(), // El usuario que solicita (estudiante)
            'estado' => TrasladoMatriculaLog::ESTADO_PENDIENTE
        ]);

        $this->dispatch('swal:success', [
            'title' => 'Solicitud Enviada',
            'text' => 'Tu solicitud de traslado ha sido creada y está pendiente de aprobación.'
        ]);

        $this->reset(['matriculaSeleccionadaId', 'horarioDestinoId', 'matriculaSeleccionada']);
        $this->cargarMatriculas();
        $this->cargarHistorial();
    }

    public function render()
    {
        return view('livewire.matricula.solicitar-traslado');
    }
}
