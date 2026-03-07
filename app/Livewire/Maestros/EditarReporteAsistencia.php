<?php

namespace App\Livewire\Maestros;

use Livewire\Component;
use App\Models\ReporteAsistenciaClase;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Maestro;
use App\Models\User;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;
use App\Models\MotivoInasistencia;
use App\Models\ReporteAsistenciaAlumnos as DetalleAsistenciaModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // IMPORTANTE: Añadir DB para la búsqueda

class EditarReporteAsistencia extends Component
{
    // PROPIEDADES PÚBLICAS
    public HorarioMateriaPeriodo $horarioAsignado;
    public Maestro $maestro;
    public ReporteAsistenciaClase $reporte;

    // Propiedad pública para el término de búsqueda
    public string $busqueda = '';

    // PROPIEDADES PARA EL FORMULARIO
    public $alumnosDelHorario = [];
    public $asistencias = [];
    public $motivosInasistencia = [];

    // Propiedades para manejar el modal de observaciones
    public bool $mostrarModalObservacion = false;
    public ?int $alumnoIdParaObservacion = null;
    public string $observacionActual = '';
    public string $nombreAlumnoModal = '';

    public function mount(HorarioMateriaPeriodo $horarioAsignado, Maestro $maestro, ReporteAsistenciaClase $reporte)
    {
        $this->horarioAsignado = $horarioAsignado;
        $this->maestro = $maestro;
        $this->reporte = $reporte;
        $this->cargarMotivosInasistencia();
        // Cargamos todos los alumnos y sus asistencias UNA SOLA VEZ para mantener el estado completo.
        $this->cargarDatosDelReporte();
    }

    public function cargarMotivosInasistencia()
    {
        $this->motivosInasistencia = MotivoInasistencia::orderBy('nombre')->get();
    }

    public function cargarDatosDelReporte()
    {
        $this->reporte->load('detallesAsistencia');
        $this->alumnosDelHorario = EstadoAcademico::where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->with(['user' => function ($query) {
                $query->select('id', 'primer_nombre', 'primer_apellido', 'identificacion', 'genero')
                    ->orderBy('primer_nombre', 'asc')
                    ->orderBy('primer_apellido', 'asc');
            }])
            ->get()
            ->map(fn($estado) => $estado->user)
            ->filter();

        $this->asistencias = [];
        $detallesExistentes = $this->reporte->detallesAsistencia->keyBy('user_id');

        foreach ($this->alumnosDelHorario as $alumno) {
            if ($alumno) {
                $detalle = $detallesExistentes->get($alumno->id);
                $this->asistencias[$alumno->id] = [
                    'asistio' => $detalle ? $detalle->asistio : false,
                    'motivo_inasistencia_id' => $detalle ? $detalle->motivo_inasistencia_id : null,
                    'observaciones_alumno' => $detalle ? $detalle->observaciones_alumno : '',
                    'auto_asistencia'=> $detalle ? $detalle->auto_asistencia : false,
                ];
            }
        }
    }

    protected function rules()
    {
        $rules = [];
        if (!empty($this->alumnosDelHorario)) {
            foreach ($this->alumnosDelHorario as $alumno) {
                if ($alumno) {
                    $alumnoId = $alumno->id;
                    $rules["asistencias.{$alumnoId}.asistio"] = 'required|boolean';
                    $rules["asistencias.{$alumnoId}.motivo_inasistencia_id"] = [
                        'nullable',
                        Rule::requiredIf(fn() => isset($this->asistencias[$alumnoId]['asistio']) && $this->asistencias[$alumnoId]['asistio'] === false),
                        'exists:motivos_inasistencias_reporte_escuelas,id',
                    ];
                }
            }
        }
        $rules['observacionActual'] = 'nullable|string|max:2000';
        return $rules;
    }

    protected $messages = [
        'asistencias.*.motivo_inasistencia_id.requiredIf' => 'El motivo es requerido.',
        'observacionActual.max' => 'La observación no puede exceder los 2000 caracteres.',
    ];

    public function updatedAsistencias($value, $key)
    {
        $parts = explode('.', $key);
        $alumnoId = (int)$parts[0];
        $field = $parts[1];
        if ($field === 'asistio') {
            if ($value == '1') { // Si se marca "Sí asistió"
                $this->asistencias[$alumnoId]['motivo_inasistencia_id'] = null;
            }
            $this->guardarAsistenciaDeUnAlumno($alumnoId);
        }
    }

    public function guardarAsistenciaDeUnAlumno($alumnoId)
    {
        $this->validate([
            "asistencias.{$alumnoId}.asistio" => 'required|boolean',
            "asistencias.{$alumnoId}.motivo_inasistencia_id" => ['nullable'],
        ]);
        $dataAsistencia = $this->asistencias[$alumnoId];
        DetalleAsistenciaModel::updateOrCreate(
            ['reporte_asistencia_clase_id' => $this->reporte->id, 'user_id' => $alumnoId],
            ['asistio' => $dataAsistencia['asistio'], 'motivo_inasistencia_id' => !$dataAsistencia['asistio'] ? ($dataAsistencia['motivo_inasistencia_id'] ?: null) : null]
        );
    }

    public function abrirModalObservacion($alumnoId)
    {
        $alumno = $this->alumnosDelHorario->firstWhere('id', $alumnoId);
        if ($alumno) {
            $this->alumnoIdParaObservacion = $alumnoId;
            $this->nombreAlumnoModal = $alumno->primer_nombre . ' ' . $alumno->primer_apellido;
            $this->observacionActual = $this->asistencias[$alumnoId]['observaciones_alumno'] ?? '';
            $this->mostrarModalObservacion = true;
        }
    }

    public function guardarObservacion()
    {
        $this->validate(['observacionActual' => 'nullable|string|max:2000']);
        $alumnoId = $this->alumnoIdParaObservacion;
        if ($alumnoId) {
            $dataAsistencia = $this->asistencias[$alumnoId];
            DetalleAsistenciaModel::updateOrCreate(
                ['reporte_asistencia_clase_id' => $this->reporte->id, 'user_id' => $alumnoId],
                ['asistio' => $dataAsistencia['asistio'], 'motivo_inasistencia_id' => !$dataAsistencia['asistio'] ? ($dataAsistencia['motivo_inasistencia_id'] ?: null) : null, 'observaciones_alumno' => $this->observacionActual]
            );
            $this->asistencias[$alumnoId]['observaciones_alumno'] = $this->observacionActual;
            $this->cerrarModalObservacion();
        }
    }

    public function cerrarModalObservacion()
    {
        $this->reset(['mostrarModalObservacion', 'alumnoIdParaObservacion', 'observacionActual', 'nombreAlumnoModal']);
    }

    public function guardarYFinalizar()
    {
        $this->validate($this->rules());
        foreach ($this->alumnosDelHorario as $alumno) {
            if ($alumno) {
                $dataAsistencia = $this->asistencias[$alumno->id];
                DetalleAsistenciaModel::updateOrCreate(
                    ['reporte_asistencia_clase_id' => $this->reporte->id, 'user_id' => $alumno->id],
                    ['asistio' => $dataAsistencia['asistio'], 'motivo_inasistencia_id' => !$dataAsistencia['asistio'] ? ($dataAsistencia['motivo_inasistencia_id'] ?: null) : null, 'observaciones_alumno' => $dataAsistencia['observaciones_alumno'], 'auto_asistencia' => $dataAsistencia['auto_asistencia']]
                );
            }
        }
        $this->reporte->estado_reporte = 'completado';
        $this->reporte->reportado_por_user_id = Auth::id();
        $this->reporte->save();

        return redirect()->route('maestros.reporteAsistencia', ['maestro' => $this->maestro->id, 'horarioAsignado' => $this->horarioAsignado->id])
                        ->with('success', 'Reporte de asistencia finalizado y guardado correctamente.');
    }

    public function render()
    {
        $alumnosParaLaVista = $this->alumnosDelHorario;

        if (!empty(trim($this->busqueda))) {
            $terminoBusqueda = '%' . str_replace(' ', '%', trim($this->busqueda)) . '%';

            // Filtramos la colección de alumnos ya cargada en memoria
            $alumnosParaLaVista = $this->alumnosDelHorario->filter(function ($alumno) use ($terminoBusqueda) {
                $nombreCompleto = strtolower($alumno->primer_nombre . ' ' . $alumno->primer_apellido);
                $identificacion = $alumno->identificacion;
                $termino = strtolower(str_replace('%', '', $terminoBusqueda));
                
                // Devuelve true si el término de búsqueda está en el nombre completo o en la identificación
                return str_contains($nombreCompleto, $termino) || str_contains($identificacion, $termino);
            });
        }

        return view('livewire.maestros.editar-reporte-asistencia', [
            'alumnosParaLaVista' => $alumnosParaLaVista
        ]);
    }
}