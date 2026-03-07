<?php

namespace App\Livewire\Informes;

use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformePagosExport;
use App\Models\Pago;
use App\Models\Grupo;
use App\Models\User;
use App\Models\Moneda;
use App\Models\Destinatario;
use App\Models\Actividad;
use App\Models\TipoPago;
use App\Models\EstadoPago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

class InformePagos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Active Filters (Used for Query)
    public $activeFilters = [];

    // Draft Filters (Bound to Inputs)
    public $actividad_id;
    public $grupo_id;
    public $user_id; // "Asistente" (dueño de la compra)
    public $destinatario_ids = [];
    public $estado_pago_id; // Estado del PAGO, no de la compra
    public $tipo_pago_id;
    public $moneda_id;
    public $fecha_inicio;
    public $fecha_fin;

    // Search inputs (for autocomplete dropdowns)
    public $search_actividad = '';
    public $search_grupo = '';
    public $search_user = '';

    // Selected labels (for display)
    public $actividad_seleccionada_nombre;
    public $grupo_seleccionado_nombre;
    public $user_seleccionado_nombre;

    // Reset Token for Child Components
    public $resetToken = 0;

    protected $listeners = [
        'usuario-seleccionado' => 'fijarUsuario',
        'grupo-id-anidado' => 'fijarGrupo'
    ];

    #[Renderless]
    public function fijarUsuario($id)
    {
        $this->user_id = $id;
        $this->user_seleccionado_nombre = null;
        if ($id) {
             $u = User::find($id);
             $this->user_seleccionado_nombre = $u ? $u->nombre_completo : '';
        } else {
             $this->user_seleccionado_nombre = null;
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
        } else {
             $this->grupo_seleccionado_nombre = null;
        }
    }

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
            'estado_pago_id' => null,
            'tipo_pago_id' => null,
            'moneda_id' => null,
            'fecha_inicio' => $defaultInicio,
            'fecha_fin' => $defaultFin,
        ];

        // Initialize Draft Filters
        $this->resetDrafts();
    }

    public function resetDrafts()
    {
        // Reset all draft inputs to empty/null or defaults
        $this->actividad_id = null;
        $this->grupo_id = null;
        $this->user_id = null;
        $this->destinatario_ids = [];
        $this->estado_pago_id = null;
        $this->tipo_pago_id = null;
        $this->moneda_id = null;

        // Reset dates
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
            'estado_pago_id' => $this->estado_pago_id,
            'tipo_pago_id' => $this->tipo_pago_id,
            'moneda_id' => $this->moneda_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
        ];

        $this->resetPage(); // Reset pagination on new filter
        $this->dispatch('close-offcanvas'); // Custom event to close UI

        // Reset drafts for next time
        $this->resetDrafts();
    }

    public function render()
    {
        $pagos = $this->obtenerPagos();
        $totales = $this->calcularTotales();

        return view('livewire.informes.informe-pagos', [
            'pagos' => $pagos,
            'totales' => $totales,
            'monedas' => Moneda::all(),
            'destinatarios' => Destinatario::all(),
            'tipos_pago' => TipoPago::all(),
            'estados_pago' => EstadoPago::all(),
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
                'label' => 'Usuario: ' . (User::find($f['user_id'])?->nombre_completo ?? 'Seleccionado'),
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

        if (!empty($f['estado_pago_id'])) {
            $estado = EstadoPago::find($f['estado_pago_id']);
            $tags[] = [
                'label' => 'Estado: ' . ($estado ? $estado->nombre : 'Seleccionado'),
                'field' => 'estado_pago_id'
            ];
        }

        if (!empty($f['tipo_pago_id'])) {
            $tipo = TipoPago::find($f['tipo_pago_id']);
            $tags[] = [
                'label' => 'Tipo: ' . ($tipo ? $tipo->nombre : 'Seleccionado'),
                'field' => 'tipo_pago_id'
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
                'estado_pago_id' => null,
                'tipo_pago_id' => null,
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

    private function obtenerPagosQuery()
    {
        // Empezamos desde Pagos y traemos la Compra asociada
        $query = Pago::with(['compra.user.gruposDondeAsiste', 'compra.actividad', 'moneda', 'tipoPago', 'estadoPago'])
            ->orderBy('fecha', 'desc');

        $f = $this->activeFilters; // Use Active Filters

        // Filtro por Actividad (a través de Compra)
        if (!empty($f['actividad_id'])) {
            $query->whereHas('compra', function($q) use ($f) {
                $q->where('actividad_id', $f['actividad_id']);
            });
        }

        // Filtro por Usuario (El que hizo la compra)
        if (!empty($f['user_id'])) {
            $query->whereHas('compra', function($q) use ($f) {
                $q->where('user_id', $f['user_id']);
            });
        }

        // Filtro por Grupo (del Usuario de la compra)
        if (!empty($f['grupo_id'])) {
            $query->whereHas('compra.user.gruposDondeAsiste', function($q) use ($f) {
                $q->where('grupos.id', $f['grupo_id']);
            });
        }

        // Filtro por Destinatario/Sucursal (a través de Compra)
        if (!empty($f['destinatario_ids'])) {
            $query->whereHas('compra', function($q) use ($f) {
                $q->whereIn('destinatario_id', $f['destinatario_ids']);
            });
        }

        // Filtro por Estado del PAGO
        if (!empty($f['estado_pago_id'])) {
            $query->where('estado_pago_id', $f['estado_pago_id']);
        }

        // Filtro por Tipo/Metodo de Pago
        if (!empty($f['tipo_pago_id'])) {
            $query->where('tipo_pago_id', $f['tipo_pago_id']);
        }

        // Filtro por Moneda
        if (!empty($f['moneda_id'])) {
            $query->where('moneda_id', $f['moneda_id']);
        }

        // Filtro por Fecha del Pago
        if (!empty($f['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $f['fecha_inicio']);
        }

        if (!empty($f['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $f['fecha_fin']);
        }

        return $query;
    }

    private function obtenerPagos()
    {
        return $this->obtenerPagosQuery()->paginate(9);
    }

    private function calcularTotales()
    {
        $query = $this->obtenerPagosQuery();

        // Agrupar por moneda y sumar 'valor' del pago
        $stats = $query->get()->groupBy('moneda_id')->map(function ($pagos) {
            $moneda = $pagos->first()->moneda;
            return [
                'moneda' => $moneda ? $moneda->nombre : 'Desconocida',
                'total' => $pagos->sum('valor'),
                'count' => $pagos->count()
            ];
        });

        return $stats;
    }

    public function exportarExcel()
    {
        // Pass Active Filters to Export
        return Excel::download(new InformePagosExport($this->activeFilters), 'informe_pagos_' . now()->format('Y-m-d_H-i') . '.xlsx');
    }
}
