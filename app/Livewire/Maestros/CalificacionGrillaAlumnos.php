<?php

namespace App\Livewire\Maestros;

use Livewire\Component;
use App\Models\HorarioMateriaPeriodo;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Periodo;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\AlumnoRespuestaItem;
use App\Models\CortePeriodo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalificacionGrillaAlumnos extends Component
{
    public HorarioMateriaPeriodo $horarioAsignado;
    public Collection $alumnosConEstado;
    public Collection $items;
    public $configuracion;
    public $periodo;
    public array $notas = [];
    public $rolActivo;
    public $puedeCalificarSinFecha;
    public Carbon $fechaActual;

    protected $messages = [
        'notas.*.*.numeric' => 'Debe ser un número.',
        'notas.*.*.min' => 'Nota muy baja.',
        'notas.*.*.max' => 'Nota muy alta.',
    ];

    public function mount(HorarioMateriaPeriodo $horarioAsignado)
    {
        $this->horarioAsignado = $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo',
        ]);
        $this->periodo = $this->horarioAsignado->materiaPeriodo->periodo;

        $user = Auth::user();
        $this->rolActivo = $user ? $user->roles()->wherePivot('activo', true)->first() : null;
        $this->puedeCalificarSinFecha = $this->rolActivo ? $this->rolActivo->hasPermissionTo('escuelas.calificar_cualquier_fecha') : false;
        $this->configuracion = Configuracion::find(1);
        $this->fechaActual = Carbon::now()->startOfDay();

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // 1. Cargar Items ordenados cronológicamente y por nombre
        $this->items = ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $this->horarioAsignado->id)

            // Filtro de fecha inicio <= hoy, como en el ejemplo
            ->when(!($this->rolActivo && $this->rolActivo->hasPermissionTo('escuelas.es_administrativo')), function ($q) {
                $q->whereDate('fecha_inicio', '<=', $this->fechaActual);
            })
            ->with(['cortePeriodo.corteEscuela'])
            ->orderBy('fecha_inicio', 'asc')
            ->orderBy('orden', 'asc')
            ->orderBy('nombre', 'asc') // Fallback sort
            ->get();

        // 2. Cargar Alumnos
        $query = EstadoAcademico::query()
            ->where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->with(['user:id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,identificacion,email,foto', 'matricula'])
            ->join('users', 'matricula_horario_materia_periodo.user_id', '=', 'users.id')
            ->select('matricula_horario_materia_periodo.*')
            ->orderBy('users.primer_apellido', 'asc')
            ->orderBy('users.primer_nombre', 'asc');

        $this->alumnosConEstado = $query->get();

        // 3. Cargar Notas Existentes
        // Obtenemos todas las respuestas de estos alumnos para estos items de una sola vez
        if ($this->alumnosConEstado->isEmpty() || $this->items->isEmpty()) {
            return;
        }

        $alumnoIds = $this->alumnosConEstado->pluck('user_id');
        $itemIds = $this->items->pluck('id');

        $respuestas = AlumnoRespuestaItem::whereIn('user_id', $alumnoIds)
            ->whereIn('item_corte_materia_periodo_id', $itemIds)
            ->get();

        // Mapear a la estructura de array para el binding de Livewire
        foreach ($respuestas as $respuesta) {
            // Convertimos a string para el input y manejamos decimales
            $this->notas[$respuesta->user_id][$respuesta->item_corte_materia_periodo_id] =
                $respuesta->nota_obtenida !== null ? (string)$respuesta->nota_obtenida : '';
        }
    }

    protected function rules()
    {
        // Reglas dinámicas
        $rules = [];
        if ($this->configuracion) {
            foreach ($this->notas as $userId => $items) {
                foreach ($items as $itemId => $nota) {
                    $rules["notas.{$userId}.{$itemId}"] = ['nullable', 'numeric', 'min:' . ($this->configuracion->nota_minima ?? 0), 'max:' . ($this->configuracion->nota_maxima ?? 5)];
                }
            }
        }
        return $rules;
    }

    public function updatedNotas($notaIngresada, $key)
    {
        // $key viene como "userId.itemId"
        $keys = explode('.', $key);
        if (count($keys) === 2 && is_numeric($keys[0]) && is_numeric($keys[1])) {
            $this->guardarCalificacion($keys[0], $keys[1], $notaIngresada);
        }
    }

    public function guardarCalificacion($userId, $itemId, $notaIngresada)
    {
        // Validar item
        $item = $this->items->find($itemId);
        if (!$item) return;

        // Validar Fechas (Corte Cerrado)
        if (!$this->puedeCalificarSinFecha) {
            $fechaFinItem = $item->fecha_fin ? Carbon::parse($item->fecha_fin)->endOfDay() : null;
            // O checkear fecha fin del corte
            $corte = $item->cortePeriodo;
            $fechaFinCorte = $corte->fecha_fin ? Carbon::parse($corte->fecha_fin)->endOfDay() : null;

            // Prioridad a la fecha del corte si el item no tuviera (o la lógica que prefieras, usualmente corte manda)
            $limite = $fechaFinCorte ?? $fechaFinItem;

            if ($limite && $this->fechaActual->gt($limite)) {
                $this->dispatch('mostrarErrorConSweetAlert', ['texto' => 'El plazo para calificar este ítem (o su corte) ha vencido.']);
                 // Revertir valor en vista
                 $originalValue = AlumnoRespuestaItem::where('user_id', $userId)->where('item_corte_materia_periodo_id', $itemId)->value('nota_obtenida');
                 $this->notas[$userId][$itemId] = $originalValue !== null ? (string)$originalValue : '';
                 return;
            }
        }

        // Validación de valor
        $this->validateOnly("notas.{$userId}.{$itemId}");

        $notaAjustada = ($notaIngresada === '' || $notaIngresada === null) ? null : (float) str_replace(',', '.', $notaIngresada);

        try {
            AlumnoRespuestaItem::updateOrCreate(
                ['user_id' => $userId, 'item_corte_materia_periodo_id' => $itemId],
                ['nota_obtenida' => $notaAjustada, 'calificador_user_id' => Auth::id(), 'fecha_calificacion' => now()]
            );

            // Feedback sutil
             $this->dispatch('notaGuardada', ['userId' => $userId, 'itemId' => $itemId]);

        } catch (\Exception $e) {
            Log::error("Error guardando nota grilla: " . $e->getMessage());
            $this->dispatch('mostrarErrorConSweetAlert', ['texto' => 'Error al guardar.']);
        }
    }

    public function render()
    {
        return view('livewire.maestros.calificacion-grilla-alumnos');
    }
}
