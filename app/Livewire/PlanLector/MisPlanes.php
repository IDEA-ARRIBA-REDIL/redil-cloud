<?php

namespace App\Livewire\PlanLector;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PlanLector;
use App\Models\PlanLectorCategoria;
use App\Models\Configuracion;
use Illuminate\Support\Facades\DB;

class MisPlanes extends Component
{
    use WithPagination;

    // Use specific pagination theme if necessary, bootstrap is default in this template
    protected $paginationTheme = 'bootstrap';

    public $pestanaActiva = 'inscrito'; // 'inscrito' o 'completado'
    public $buscar = '';
    public $categoriaSeleccionada = null; // null = Todos
    public $ordenarPor = 'nuevos'; // nuevos, populares, valorados, breves, extensos

    public function cambiarPestana($estado)
    {
        $this->pestanaActiva = $estado;
        $this->resetPage('mis_planes_page');
    }

    public function seleccionarCategoria($idCategoria)
    {
        // Si viene como string 'null' desde el frontend (posible en Livewire 3), lo tratamos como null
        $this->categoriaSeleccionada = ($idCategoria === 'null' || !$idCategoria) ? null : $idCategoria;
        $this->resetPage('explorador_page');
        
        // Disparamos evento para recargar swiper si es necesario o manejar estilos
        $this->dispatch('categoriaCambiada');
    }

    public function updatingBuscar()
    {
        $this->resetPage('explorador_page');
    }

    public function updatingOrdenarPor()
    {
        $this->resetPage('explorador_page');
    }

    public function rendirse()
    {
        // En caso de que se necesite limpiar filtros (opcional)
        $this->buscar = '';
        $this->categoriaSeleccionada = null;
        $this->ordenarPor = 'nuevos';
    }

    public function abandonarPlan($planId)
    {
        $usuario = auth()->user();
        $plan = PlanLector::findOrFail($planId);

        DB::transaction(function () use ($usuario, $plan) {
            // 1. Obtener días del plan para limpiar progreso
            $diasIds = $plan->dias()->pluck('id');

            // 2. Borrar progreso diario
            DB::table('plan_lector_dia_users')
                ->where('user_id', $usuario->id)
                ->whereIn('plan_lector_dia_id', $diasIds)
                ->delete();

            // 3. Borrar la suscripción (pivote)
            DB::table('plan_lector_users')
                ->where('user_id', $usuario->id)
                ->where('plan_lector_id', $plan->id)
                ->delete();

            // 4. Recalcular promedio del plan (centralizado en el modelo)
            $plan->recalcularPromedio();
        });

        $this->dispatch('alert', 
            icon: 'success', 
            title: '¡Plan abandonado!', 
            text: 'Tu progreso ha sido eliminado correctamente.'
        );
    }

    public function render()
    {
        $usuario = auth()->user();

        // 1. MIS PLANES (Dependiendo de la pestaña)
        $planesInscritos = $usuario->planesLectoresInscritos()
            ->with(['autor', 'categorias'])
            ->withCount('dias')
            ->wherePivot('estado', $this->pestanaActiva)
            ->orderByPivot('updated_at', 'desc')
            ->paginate(4, ['*'], 'mis_planes_page');

        // IDs para excluir del explorador
        $idsInscritos = $usuario->planesLectoresInscritos()->pluck('planes_lectores.id');

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
                        
                        // Normalizamos acentos tanto en la columna como en el término de búsqueda para PostgreSQL
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

        $planesDisponibles = $planesDisponiblesQuery->paginate(12, ['*'], 'explorador_page');

        $categorias = PlanLectorCategoria::orderBy('nombre')->get();
        $configuracion = Configuracion::find(1);

        return view('livewire.plan-lector.mis-planes', [
            'pestanaActiva' => $this->pestanaActiva,
            'planesInscritos' => $planesInscritos,
            'planesDisponibles' => $planesDisponibles,
            'categorias' => $categorias,
            'configuracion' => $configuracion
        ]);
    }
}
