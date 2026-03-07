<?php

namespace App\Livewire\Informes;

use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeComprasExport;
use App\Models\Compra;
use App\Models\Actividad;
use App\Models\Grupo;
use App\Models\User;
use App\Models\Moneda;
use App\Models\Destinatario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

class InformeCompras extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Active Filters (Used for Query)
    public $activeFilters = [];

    // Draft Filters (Bound to Inputs)
    public $actividad_id;
    public $grupo_id;
    public $user_id; // "Asistente"
    public $destinatario_ids = []; // Array for multiple selection
    public $estado; // 1=Pendiente, 2=Pagada, 3=Anulada
    public $moneda_id;
    public $fecha_inicio;
    public $fecha_fin;

    // Search inputs (for autocomplete dropdowns)
    public $search_actividad = '';
    public $search_grupo = '';
    public $search_user = '';

    // Reset Token for Child Components
    public $resetToken = 0;

    // Selected labels (for Draft display)
    public $actividad_seleccionada_nombre;
    public $grupo_seleccionado_nombre;
    public $user_seleccionado_nombre;

    protected $listeners = [
        'usuario-seleccionado' => 'fijarUsuario',
        'grupo-id-anidado' => 'fijarGrupo'
    ];

    public function mount()
    {
        // Default dates: This month
        $defaultInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $defaultFin = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Initialize Active Filters
        $this->activeFilters = [
            'actividad_id' => null,
            'grupo_id' => null,
            'user_id' => null,
            'destinatario_ids' => [],
            'estado' => null,
            'moneda_id' => null,
            'fecha_inicio' => $defaultInicio,
            'fecha_fin' => $defaultFin,
        ];

        // Initialize Draft Filters (Reset to clean state as requested)
        $this->resetDrafts();
    }

    public function resetDrafts()
    {
        // Reset all draft inputs to empty/null or defaults for inputs
        $this->actividad_id = null;
        $this->grupo_id = null;
        $this->user_id = null;
        $this->destinatario_ids = [];
        $this->estado = null;
        $this->moneda_id = null;

        // Reset dates to current month defaults for the form interaction
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->actividad_seleccionada_nombre = null;
        $this->grupo_seleccionado_nombre = null;
        $this->user_seleccionado_nombre = null;

        $this->search_actividad = '';
        $this->search_grupo = '';
        $this->search_user = '';

        // Dispatch events to clear front-end components if needed
        $this->dispatch('limpiarFiltroActividad');
        $this->dispatch('limpiarFiltroSucursales');

        // Increment token to force re-render of child components
        $this->resetToken++;
    }

    public function aplicarFiltros()
    {
        $this->activeFilters = [
            'actividad_id' => $this->actividad_id,
            'grupo_id' => $this->grupo_id,
            'user_id' => $this->user_id,
            'destinatario_ids' => $this->destinatario_ids,
            'estado' => $this->estado,
            'moneda_id' => $this->moneda_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
        ];

        $this->resetPage(); // Reset pagination on new filter
        $this->dispatch('close-offcanvas'); // Custom event to close UI

        // Reset drafts for next time the user opens the menu
        $this->resetDrafts();
    }

    #[Renderless]
    public function fijarUsuario($id)
    {
        $this->user_id = $id;
        $this->user_seleccionado_nombre = null;
        if ($id) {
             $u = User::find($id);
             $this->user_seleccionado_nombre = $u ? $u->nombre_completo : '';
        }
    }

    #[Renderless]
    public function fijarGrupo($grupoId)
    {
        $this->grupo_id = $grupoId;
        $this->grupo_seleccionado_nombre = null;
        if ($grupoId) {
             $g = Grupo::find($grupoId);
             $this->grupo_seleccionado_nombre = $g ? $g->nombre : '';
        }
    }

    public function render()
    {
        $compras = $this->obtenerCompras();
        $totales = $this->calcularTotales();

        return view('livewire.informes.informe-compras', [
            'compras' => $compras,
            'totales' => $totales,
            'monedas' => Moneda::all(),
            'destinatarios' => Destinatario::all(),
            'actividades' => Actividad::select('id', 'nombre')->orderBy('created_at', 'desc')->get(),
            // Search results (limited) - These use DRAFT inputs for search
            'resultados_grupo' => $this->search_grupo ? Grupo::where('nombre', 'like', '%' . $this->search_grupo . '%')->take(10)->get() : [],
            'resultados_user' => $this->search_user ? User::where(DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"), 'like', '%' . $this->search_user . '%')->orWhere('identificacion', 'like', '%' . $this->search_user . '%')->take(10)->get() : [],
        ]);
    }

    public function seleccionarActividad($id, $nombre)
    {
        $this->actividad_id = $id;
        $this->actividad_seleccionada_nombre = $nombre;
        $this->search_actividad = '';
    }

    public function seleccionarGrupo($id, $nombre)
    {
        $this->grupo_id = $id;
        $this->grupo_seleccionado_nombre = $nombre;
        $this->search_grupo = '';
    }

    public function seleccionarUser($id, $nombre)
    {
        $this->user_id = $id;
        $this->user_seleccionado_nombre = $nombre;
        $this->search_user = '';
    }

    public function getTagsProperty()
    {
        $tags = [];
        $f = $this->activeFilters; // Use Active Filters

        if (!empty($f['actividad_id'])) {
            $tags[] = [
                'label' => 'Actividad: ' . (Actividad::find($f['actividad_id'])?->nombre ?? 'Seleccionada'),
                'field' => 'actividad_id'
            ];
        }

        if (!empty($f['grupo_id'])) {
            $tags[] = [
                'label' => 'Grupo: ' . (Grupo::find($f['grupo_id'])?->nombre ?? 'Seleccionado'),
                'field' => 'grupo_id'
            ];
        }

        if (!empty($f['user_id'])) {
            $tags[] = [
                'label' => 'Asistente: ' . (User::find($f['user_id'])?->nombre_completo ?? 'Seleccionado'),
                'field' => 'user_id'
            ];
        }

        if (!empty($f['destinatario_ids'])) {
            $nombres = Destinatario::whereIn('id', $f['destinatario_ids'])->pluck('nombre')->implode(', ');
            $tags[] = [
                'label' => 'Sucursales: ' . Str::limit($nombres, 30),
                'field' => 'destinatario_ids'
            ];
        }

        if (!empty($f['moneda_id'])) {
            $moneda = Moneda::find($f['moneda_id']);
            $tags[] = [
                'label' => 'Moneda: ' . ($moneda ? $moneda->nombre : ''),
                'field' => 'moneda_id'
            ];
        }

        if (!empty($f['estado'])) {
            $estadoLabel = match($f['estado']) {
                '1' => 'Pendiente',
                '2' => 'Pagada',
                '3' => 'Anulada',
                '4' => 'Abonada',
                default => 'Estado'
            };
            $tags[] = [
                'label' => 'Estado: ' . $estadoLabel,
                'field' => 'estado'
            ];
        }

        if (!empty($f['fecha_inicio'])) {
            $tags[] = [
                'label' => 'Desde: ' . $f['fecha_inicio'],
                'field' => 'fecha_inicio'
            ];
        }

        if (!empty($f['fecha_fin'])) {
            $tags[] = [
                'label' => 'Hasta: ' . $f['fecha_fin'],
                'field' => 'fecha_fin'
            ];
        }

        return $tags;
    }

    public function limpiarFiltro($filtro)
    {
        if ($filtro === 'todos') {
            $this->activeFilters = [
                'actividad_id' => null,
                'grupo_id' => null,
                'user_id' => null,
                'destinatario_ids' => [],
                'estado' => null,
                'moneda_id' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ];
        } elseif (array_key_exists($filtro, $this->activeFilters)) {
            $this->activeFilters[$filtro] = null;
             // Special handling if array
            if ($filtro === 'destinatario_ids') $this->activeFilters[$filtro] = [];
        }
    }

    private function obtenerComprasQuery()
    {
        $query = Compra::with(['user.gruposDondeAsiste', 'actividad', 'moneda', 'destinatario', 'inscripciones', 'pagos', 'categorias'])
            ->orderBy('fecha', 'desc');

        $f = $this->activeFilters; // Use Active Filters

        if (!empty($f['actividad_id'])) {
            $query->where('actividad_id', $f['actividad_id']);
        }

        if (!empty($f['user_id'])) {
            $query->where('user_id', $f['user_id']);
        }

        if (!empty($f['grupo_id'])) {
            $groupId = $f['grupo_id'];
            $query->whereHas('user.gruposDondeAsiste', function($q) use ($groupId) {
                $q->where('grupos.id', $groupId);
            });
        }

        if (!empty($f['destinatario_ids'])) {
            $query->whereIn('destinatario_id', $f['destinatario_ids']);
        }

        if (!empty($f['estado'])) {
            $estado = $f['estado'];

            // 4 = Abonada
            if ($estado == '4') {
                $query->whereHas('abonos');
            } else {
                $query->whereHas('estadoPago', function ($q) use ($estado) {
                    if ($estado == '1') { // Pendiente
                        $q->where('estado_pendiente', true);
                    } elseif ($estado == '2') { // Pagada/Finalizada
                        $q->where('estado_final_inscripcion', true);
                    } elseif ($estado == '3') { // Anulada
                        $q->where('estado_anulado_inscripcion', true);
                    }
                });
            }
        }

        if (!empty($f['moneda_id'])) {
            $query->where('moneda_id', $f['moneda_id']);
        }

        if (!empty($f['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $f['fecha_inicio']);
        }

        if (!empty($f['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $f['fecha_fin']);
        }

        return $query;
    }

    private function obtenerCompras()
    {
        return $this->obtenerComprasQuery()->paginate(9);
    }

    private function calcularTotales()
    {
        $query = $this->obtenerComprasQuery();

        $stats = $query->get()->groupBy('moneda_id')->map(function ($compras) {
            $moneda = $compras->first()->moneda;
            return [
                'moneda' => $moneda ? $moneda->nombre : 'Desconocida',
                'total' => $compras->sum('valor'),
                'count' => $compras->count()
            ];
        });

        return $stats;
    }

    public function exportarExcel()
    {
        // Pass Active Filters to Export
        return Excel::download(new InformeComprasExport($this->activeFilters), 'informe_compras_' . now()->format('Y-m-d_H-i') . '.xlsx');
    }
}
