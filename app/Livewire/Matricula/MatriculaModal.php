<?php

namespace App\Livewire\Matricula;

use Livewire\Component;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;
use App\Services\MatriculaService;
use Livewire\Attributes\On;

class MatriculaModal extends Component
{
    public $showModal = false;
    public $usuario;
    public $materia;
    public $configuracion;

    // --- IDs para los selects ---
    public $materiaId;
    public $periodoId;
    public $sedeId;
    public $horarioId;
    public $escuelaId; // <-- CAMBIO: Nueva propiedad para guardar el ID de la escuela.
    public $usuarioId;

    // --- Campos del formulario ---
    public $estadoPago = 'pendiente';
    public $observacion = '';

    // --- Colecciones para llenar los selects ---
    public $periodos = [];
    public $sedes = [];
    public $horarios = [];

    // CAMBIO: El método ahora acepta el tercer parámetro 'escuelaId'.
    #[On('abrirModalMatricula')]
    public function openModal($materiaId, $usuarioId, $escuelaId)
    {
        // Reseteamos todos los campos.
        $this->reset(['periodoId', 'sedeId', 'horarioId', 'periodos', 'sedes', 'horarios', 'estadoPago', 'observacion', 'escuelaId']);

        // Guardamos todos los IDs necesarios.
        $this->materiaId = $materiaId;
        $this->escuelaId = $escuelaId;
        $this->usuarioId = $usuarioId; // <-- CAMBIO 2: Guardamos el ID del usuario en nuestra nueva propiedad.
        $this->configuracion=Configuracion::find(1);

        $this->materia = Materia::find($materiaId);
        $this->usuario = User::find($usuarioId);

        // CAMBIO: Hacemos la consulta de periodos más precisa, filtrando por la escuela recibida.
        // Esto asegura que solo se muestren periodos de la escuela correcta.
        $this->periodos = Periodo::where('escuela_id', $this->escuelaId)
            ->whereHas('materiasPeriodo', function ($query) {
                $query->where('materia_id', $this->materiaId);
            })
            ->orderBy('nombre', 'desc')->get();

        $this->showModal = true;
    }

    // El resto de los métodos ('updatedPeriodoId', 'updatedSedeId', 'matricular', 'closeModal')
    // no necesitan cambios, ya que se basan en las propiedades que ya hemos establecido.

    public function updatedPeriodoId($value)
    {
        $this->reset(['sedeId', 'horarioId', 'sedes', 'horarios']);
        if ($value) {
            $this->sedes = Sede::whereHas('aulas.horariosBase.horariosMateriaPeriodo', function($query) use ($value) {
                $query->whereHas('materiaPeriodo', function($q_mp) use ($value) {
                    $q_mp->where('periodo_id', $value)->where('materia_id', $this->materiaId);
                });
            })->distinct()->get();
        }
    }

    public function updatedSedeId($value)
    {
         $this->reset(['horarioId', 'horarios']);
        if ($value) {
            // Cargamos los horarios disponibles para la materia, periodo y sede seleccionados.
            $this->horarios = HorarioMateriaPeriodo::
                // CAMBIO: Añadimos 'maestros.user' al 'with' para precargar las relaciones necesarias
                // y evitar múltiples consultas a la base de datos (problema N+1).
                with(['horarioBase.aula', 'maestros.user'])
                ->whereHas('materiaPeriodo', function($query) {
                    $query->where('materia_id', $this->materiaId)->where('periodo_id', $this->periodoId);
                })
                ->whereHas('horarioBase.aula', function($query) use ($value) {
                    $query->where('sede_id', $value);
                })
                ->get();
        }

    }

    public function matricular(MatriculaService $matriculaService)
    {
        $this->validate([
            'periodoId' => 'required',
            'sedeId' => 'required',
            'horarioId' => 'required',
        ]);

        // --- VALIDACIÓN DE ÚLTIMA HORA (SEGURIDAD) ---
        // Obtenemos el reporte del servicio para asegurar que cumple todo.
        $reporte = $matriculaService->getReporteDisponibilidadMaterias($this->usuario, Materia::find($this->materiaId)->escuela)
            ->where('materia.id', $this->materiaId)
            ->first();

        if ($reporte && $reporte->estado === 'BLOQUEADA') {
            $this->dispatch('msn', [
                'msnTitulo' => 'Error de Requisitos',
                'msnTexto' => 'El estudiante no cumple con los requisitos: ' . implode(', ', $reporte->motivos),
                'msnIcono' => 'error'
            ]);
            return;
        }

        $nuevaMatricula = Matricula::create([
            'user_id' => $this->usuario->id,
            'periodo_id' => $this->periodoId,
            'horario_materia_periodo_id' => $this->horarioId,
            'fecha_matricula' => now(),
            'estado_pago_matricula' => $this->estadoPago,
            'observacion' => $this->observacion,
            'escuela_id' => $this->escuelaId,
        ]);

        EstadoAcademico::create([
            'user_id' => $this->usuario->id,
            'horario_materia_periodo_id' => $this->horarioId,
            'matricula_id' => $nuevaMatricula->id,
            'periodo_id' => $this->periodoId,
            'estado_aprobacion' => 'cursando',
        ]);

        // --- ASIGNACIÓN AUTOMÁTICA DE PASO DE CRECIMIENTO ---
        // Buscamos si la materia tiene configurado un paso para asignar "Al iniciar"
        $pasoIniciar = $this->materia->pasosCrecimiento()->wherePivot('al_iniciar', true)->first();

        if ($pasoIniciar) {
            $pasoId = $pasoIniciar->id;
            // Priorizamos el nuevo campo estado_paso_crecimiento_usuario_id, con fallback al campo estado antiguo
            $estadoId = $pasoIniciar->pivot->estado_paso_crecimiento_usuario_id ?? $pasoIniciar->pivot->estado;

            if ($pasoId && $estadoId) {
                // Sincronizamos el paso con el usuario sin borrar los que ya tenga (syncWithoutDetaching)
                $this->usuario->pasosCrecimiento()->syncWithoutDetaching([
                    $pasoId => [
                        'estado_id' => $estadoId,
                        'fecha' => now(),
                        'detalle' => 'Asignado automáticamente al matricularse en: ' . $this->materia->nombre
                    ]
                ]);
            }
        }

        $this->closeModal();



        $this->dispatch('swal:success', [
            'title' => '¡Matrícula Exitosa!',
            'text' => 'El estudiante  ha sido matriculado correctamente.',
        ]);

        $this->dispatch('recargarPagina');

    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset();
    }

    public function render()
    {
        return view('livewire.matricula.matricula-modal');
    }
}
