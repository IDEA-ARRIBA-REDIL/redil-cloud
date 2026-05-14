<?php

namespace App\Livewire\TiempoConDios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PlanLector;
use App\Models\PlanLectorCategoria;
use App\Models\PlanLectorDia;
use App\Models\Configuracion;
use App\Models\TiempoConDios;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BibliaPlanLector extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $class;
    public $name_id;
    public $respuestasPrevias;

    public $planSeleccionadoId = null;
    public $diaSeleccionadoId = null;

    // Campos para el explorador de planes
    public $buscar = '';
    public $categoriaSeleccionada = null;
    public $ordenarPor = 'nuevos';

    public function mount($class = '', $name_id = '', $respuestasPrevias = null)
    {
        $this->class = $class;
        $this->name_id = $name_id;
        $this->respuestasPrevias = $respuestasPrevias;

        $user = auth()->user();
        $fechaHoy = Carbon::now()->format('Y-m-d');
        $tiempoConDiosHoy = $user->tiemposConDios()->where('fecha', $fechaHoy)->first();

        if ($tiempoConDiosHoy) {
            if ($tiempoConDiosHoy->plan_lector_id) {
                $this->planSeleccionadoId = $tiempoConDiosHoy->plan_lector_id;
            }
            if ($tiempoConDiosHoy->plan_lector_dia_id) {
                $this->diaSeleccionadoId = $tiempoConDiosHoy->plan_lector_dia_id;
            }
        }
    }

    public function seleccionarCategoria($idCategoria)
    {
        $this->categoriaSeleccionada = ($idCategoria === 'null' || !$idCategoria) ? null : $idCategoria;
        $this->resetPage('explorador_page');
    }

    public function updatingBuscar()
    {
        $this->resetPage('explorador_page');
    }

    public function updatingOrdenarPor()
    {
        $this->resetPage('explorador_page');
    }

    public function comenzarPlan($planId)
    {
        $user = auth()->user();
        $plan = PlanLector::findOrFail($planId);

        // Si no está inscrito, inscribirlo
        $inscrito = $user->planesLectoresInscritos()->where('plan_lector_id', $planId)->exists();
        if (!$inscrito) {
            $user->planesLectoresInscritos()->attach($planId, [
                'estado' => 'inscrito',
                'fecha_inscripcion' => Carbon::now(),
                'porcentaje_progreso' => 0
            ]);
        }

        // Buscar el primer día NO completado
        $diasCompletados = DB::table('plan_lector_dia_users')
            ->where('user_id', $user->id)
            ->pluck('plan_lector_dia_id')
            ->toArray();

        $diaId = $plan->dias()->whereNotIn('id', $diasCompletados)->orderBy('dia', 'asc')->value('id');

        if (!$diaId) {
            // Ya completó todo el plan
            $this->dispatch('msn', [
                'msnTitulo' => '¡Plan completado!',
                'msnTexto' => 'Ya has completado todos los días de este plan. Por favor elige otro.',
                'msnIcono' => 'info'
            ]);
            return;
        }

        $this->planSeleccionadoId = $planId;
        $this->diaSeleccionadoId = $diaId;

        // Actualizar el borrador de tiempo con dios
        $fechaHoy = Carbon::now()->format('Y-m-d');
        $tiempoConDiosHoy = $user->tiemposConDios()->where('fecha', $fechaHoy)->first();
        if ($tiempoConDiosHoy) {
            $tiempoConDiosHoy->update([
                'plan_lector_id' => $planId,
                'plan_lector_dia_id' => $diaId
            ]);
        }
        
        $this->dispatch('cerrarModalSeleccion');
    }

    public function render()
    {
        $usuario = auth()->user();
        
        if ($this->planSeleccionadoId && $this->diaSeleccionadoId) {
            $plan = PlanLector::find($this->planSeleccionadoId);
            $dia = PlanLectorDia::with('contenidos.tipoContenido')->find($this->diaSeleccionadoId);
            
            return view('livewire.tiempo-con-dios.biblia-plan-lector', [
                'planSeleccionado' => $plan,
                'diaSeleccionado' => $dia,
            ]);
        }

        // --- Logica de listado de planes ---
        
        // 1. MIS PLANES (Solo en progreso)
        $planesInscritos = $usuario->planesLectoresInscritos()
            ->with(['autor', 'categorias'])
            ->withCount('dias')
            ->wherePivot('estado', 'inscrito')
            ->orderByPivot('updated_at', 'desc')
            ->get();

        // IDs para excluir del explorador (inscritos y completados)
        $idsInscritos = $usuario->planesLectoresInscritos()->pluck('planes_lectores.id')->toArray();

        // 2. EXPLORADOR
        $planesDisponiblesQuery = PlanLector::with(['autor', 'categorias'])
            ->withCount('dias')
            ->where('estado', true) // Solo activos
            ->whereNotIn('planes_lectores.id', $idsInscritos)
            ->forUser($usuario); 

        if (trim($this->buscar) !== '') {
            $palabras = explode(' ', trim($this->buscar));
            $planesDisponiblesQuery->where(function ($query) use ($palabras) {
                foreach ($palabras as $palabra) {
                    if (trim($palabra) !== '') {
                        $term = '%' . trim($palabra) . '%';
                        // Normalizamos acentos
                        $query->where(DB::raw("TRANSLATE(planes_lectores.titulo, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')"), 'ILIKE', DB::raw("TRANSLATE('" . $term . "', 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')"));
                    }
                }
            });
        }

        if ($this->categoriaSeleccionada) {
             $planesDisponiblesQuery->whereHas('categorias', function ($q) {
                 $q->where('plan_lector_categorias.id', $this->categoriaSeleccionada);
             });
        }

        // Aplicar ordenamiento
        $planesDisponiblesQuery->withCount('usuariosInscritos');
        
        switch ($this->ordenarPor) {
            case 'populares':
                $planesDisponiblesQuery->orderBy('usuarios_inscritos_count', 'desc');
                break;
            case 'valorados':
                $planesDisponiblesQuery->orderBy('calificacion', 'desc');
                break;
            case 'breves':
                $planesDisponiblesQuery->orderBy('dias_count', 'asc');
                break;
            case 'extensos':
                $planesDisponiblesQuery->orderBy('dias_count', 'desc');
                break;
            case 'nuevos':
            default:
                $planesDisponiblesQuery->orderBy('created_at', 'desc');
                break;
        }

        $planesDisponibles = $planesDisponiblesQuery->paginate(6, ['*'], 'explorador_page');
        $categorias = PlanLectorCategoria::orderBy('nombre')->get();
        $configuracion = Configuracion::find(1);

        return view('livewire.tiempo-con-dios.biblia-plan-lector', [
            'planSeleccionado' => null,
            'diaSeleccionado' => null,
            'planesInscritos' => $planesInscritos,
            'planesDisponibles' => $planesDisponibles,
            'categorias' => $categorias,
            'configuracion' => $configuracion
        ]);
    }
}
