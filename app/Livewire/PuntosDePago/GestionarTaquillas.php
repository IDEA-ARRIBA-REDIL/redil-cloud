<?php

namespace App\Livewire\PuntosDePago;

// --- Importaciones de Clases ---
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Caja;
use App\Models\PuntoDePago;
use App\Models\Sede;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\CajasExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AsesorPdp;

class GestionarTaquillas extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // =================================================================
    // PROPIEDADES PÚBLICAS
    // =================================================================

    // --- Propiedades para Filtros y Búsqueda ---
    public $tipo = 'todos';
    public $buscar = '';
    public $filtroSede = [];
    public $filtroPuntoDePago = [];

    // --- Propiedades para el Modal de CAJA ---
    public $modalCajaAbierto = false;
    public $esEdicion = false;
    public $cajaId;
    public $nombreCaja;
    public $puntoDePagoId;
    public $cajeroId;
    public $estadoCaja;

    public $horaApertura;
    public $horaCierre;
    public $permiteModificar;

    // --- Límite de Dinero ---
    public $limiteDinero;
    public $dineroAcumulado;

    // --- ¡NUEVAS PROPIEDADES PARA FILTROS Y TAGS! ---
    public $tagsBusqueda = [];
    public $banderaFiltros = false;
    public $filtrosActuales = []; // Para pasar los valores al formulario del Offcanvas

    // =================================================================
    // MÉTODOS DEL CICLO DE VIDA
    // =================================================================

    /**
     * ¡MODIFICADO!
     * Lee los filtros desde la URL (Request) al cargar.
     */
    public function mount(Request $request, $tipo = 'todos')
    {
        $this->tipo = $tipo;

        // Llenar los filtros desde la URL
        $this->filtrosActuales = [
            'buscar' => $request->query('buscar', ''),
            'filtroSede' => $request->query('filtroSede', []),
            'filtroPuntoDePago' => $request->query('filtroPuntoDePago', []),
        ];

        // Asignar a las propiedades públicas que usa el render()
        $this->buscar = $this->filtrosActuales['buscar'];
        $this->filtroSede = $this->filtrosActuales['filtroSede'];
        $this->filtroPuntoDePago = $this->filtrosActuales['filtroPuntoDePago'];
    }



    // =================================================================
    // MÉTODOS DEL CRUD DE CAJAS
    // =================================================================

    /**
     * Resetea el formulario del modal.
     */
    public function resetearFormularioCaja()
    {
        // Añadimos los nuevos campos al reset
        $this->reset([
            'esEdicion',
            'cajaId',
            'nombreCaja',
            'puntoDePagoId',
            'cajeroId',
            'estadoCaja',
            'horaApertura',
            'horaCierre',
            'permiteModificar',
            'limiteDinero', // <-- AÑADIDO
            'dineroAcumulado' // <-- AÑADIDO
        ]);
        $this->resetErrorBag();
    }

    /**
     * Abre el modal para CREAR una nueva caja.
     */
    public function abrirModalCrearCaja()
    {
        $this->resetearFormularioCaja();
        $this->esEdicion = false;
        $this->estadoCaja = true; // Valor por defecto

        // ¡NUEVO!
        // Establecemos valores por defecto para los nuevos campos
        $this->permiteModificar = false;
        $this->horaApertura = null;
        $this->horaCierre = null;
        $this->limiteDinero = null; // <-- AÑADIDO
        $this->dineroAcumulado = 0;

        // ¡CAMBIO!
        // Disparamos el MISMO evento que 'Editar', pero con datos nulos.
        // Esto simplifica el JS y evita conflictos de eventos.
        $this->dispatch('abrirModalEditarCaja', [
            'puntoDePagoId' => null,
            'cajeroId' => null
        ]);
    }

    /**
     * Abre el modal para EDITAR una caja existente.
     */
    public function abrirModalEditarCaja(Caja $caja)
    {
        $this->resetearFormularioCaja();
        $this->esEdicion = true;

        $this->cajaId = $caja->id;
        $this->nombreCaja = $caja->nombre;
        $this->puntoDePagoId = $caja->punto_de_pago_id;
        $this->cajeroId = $caja->user_id;
        $this->estadoCaja = $caja->estado;

        $this->horaApertura = $caja->hora_apertura ? \Carbon\Carbon::parse($caja->hora_apertura)->format('H:i') : null;
        $this->horaCierre = $caja->hora_cierre ? \Carbon\Carbon::parse($caja->hora_cierre)->format('H:i') : null;
        $this->permiteModificar = $caja->permite_modificar_registros;
        $this->limiteDinero = $caja->limite_dinero_acumulado;
        $this->dineroAcumulado = $caja->dinero_acumulado;

        $this->dispatch(
            'abrirModalEditarCaja',
            puntoDePagoId: $caja->punto_de_pago_id,
            cajeroId: $caja->user_id
        );
    }
    /**
     * Cierra el modal de Caja.
     */
    public function cerrarModalCaja()
    {
        $this->dispatch('cerrar-modal-caja');
        //$this->resetearFormularioCaja();
    }

    /**
     * Guarda (Crea o Actualiza) una Caja.
     */
    public function guardarCaja()
    {
        // ¡MODIFICADO!
        // Añadimos las nuevas reglas de validación.
        $this->validate([
            'nombreCaja' => 'required|string|max:100',
            'puntoDePagoId' => 'required|integer|exists:puntos_de_pago,id',
            'cajeroId' => 'nullable|integer|exists:users,id',
            'estadoCaja' => 'required|boolean',

            // ¡NUEVAS REGLAS!
            // 'date_format:H:i' asegura que el valor sea una hora válida (ej: 14:30).
            'horaApertura' => 'nullable|date_format:H:i',
            'horaCierre' => 'nullable|date_format:H:i',
            'permiteModificar' => 'required|boolean',
            'limiteDinero' => 'nullable|numeric|min:0', // <-- AÑADIDO

        ], [
            'nombreCaja.required' => 'El nombre es obligatorio.',
            'puntoDePagoId.required' => 'Debe seleccionar un punto de pago.',
            // Mensajes de error para las nuevas reglas
            'horaApertura.date_format' => 'La hora de apertura no tiene un formato válido.',
            'horaCierre.date_format' => 'La hora de cierre no tiene un formato válido.',
        ]);

        try {
            $mensaje = '';

            // ¡MODIFICADO!
            // Preparamos los datos para guardar (array 'data')
            $data = [
                'nombre' => $this->nombreCaja,
                'punto_de_pago_id' => $this->puntoDePagoId,
                'user_id' => $this->cajeroId ?: null,
                'estado' => $this->estadoCaja,

                // ¡NUEVOS CAMPOS!
                // Asignamos los valores de las propiedades del componente.
                'hora_apertura' => $this->horaApertura ?: null,
                'hora_cierre' => $this->horaCierre ?: null,
                'permite_modificar_registros' => $this->permiteModificar,
                'limite_dinero_acumulado' => $this->limiteDinero ?: null, // <-- AÑADIDO
            ];

            if ($this->esEdicion) {
                $caja = Caja::findOrFail($this->cajaId);
                $caja->update($data); // Usamos update() con el array
                $mensaje = 'Caja actualizada.';
            } else {
                Caja::create($data); // Usamos create() con el array
                $mensaje = 'Caja creada.';
            }

            $this->dispatch('notificacion', tipo: 'success', mensaje: $mensaje);
            $this->cerrarModalCaja();
        } catch (\Exception $e) {
            Log::error('Error al guardar caja: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Ocurrió un error al guardar.');
        }
    }

    /**
     * Muestra una alerta de confirmación (SweetAlert) antes de eliminar.
     */
    public function confirmarEliminacionCaja($id)
    {
        $this->dispatch(
            'confirmarEliminacion',
            titulo: '¿Dar de baja la Caja?',
            texto: 'Esta acción dará eliminara la caja no se podrá revertir.',
            evento: 'eliminarCajaConfirmado',
            id: $id
        );
    }

    /**
     * Elimina (Soft Delete) la caja.
     */
    #[On('eliminarCajaConfirmado')]
    public function eliminarCaja($id)
    {
        try {
            $caja = Caja::findOrFail($id);
            $caja->delete();
            // ¡DISPATCH CORREGIDO!
            $this->dispatch('notificacion', tipo: 'success', mensaje: 'Caja dada de baja.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar caja: ' . $e->getMessage());
            // ¡DISPATCH CORREGIDO!
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al dar de baja.');
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de una caja.
     */
    public function toggleEstado($id)
    {
        try {
            $caja = Caja::findOrFail($id);
            $caja->estado = !$caja->estado;
            $caja->save();

            $nuevoEstado = $caja->estado ? 'activada' : 'inactivada';
            // ¡DISPATCH CORREGIDO!
            $this->dispatch('notificacion', tipo: 'success', mensaje: "Caja {$nuevoEstado}.");
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de Caja: ' . $e->getMessage());
            // ¡DISPATCH CORREGIDO!
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al cambiar el estado.');
        }
    }

    // =================================================================
    // MÉTODO RENDER
    // =================================================================

    public function render()
    {
        // ... (Resetear tags) ...
        $this->tagsBusqueda = [];
        $this->banderaFiltros = false;

        // ... (Cargar $sedes y $puntosDePago sin cambios) ...
        $sedes = Sede::select('id', 'nombre')->orderBy('nombre')->get();
        $queryPuntos = PuntoDePago::select('id', 'nombre')->orderBy('nombre');
        if (!empty($this->filtroSede)) {
            $queryPuntos->whereIn('sede_id', $this->filtroSede);
            $nombresSedes = Sede::whereIn('id', $this->filtroSede)->pluck('nombre', 'id');
            foreach ($nombresSedes as $id => $nombre) {
                $this->tagsBusqueda[] = (object)['label' => $nombre, 'field' => 'filtroSede', 'value' => $id];
            }
            $this->banderaFiltros = true;
        }
        $puntosDePago = $queryPuntos->get();

        // ¡CONFIRMADO!
        // Esta es la lógica que solicitaste: filtrar usuarios que sean 'es_cajero'.
        $fullNameConcat = "CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido, users.segundo_apellido)";
        $usuarios = User::select('users.id', DB::raw("($fullNameConcat) as name"))
            ->join('asesores_pdp', 'users.id', '=', 'asesores_pdp.user_id')
            ->where('asesores_pdp.es_cajero', true)
            ->where('asesores_pdp.activo', true)
            ->orderBy('users.primer_nombre')
            ->get();

        $configuracion = Configuracion::first();

        // ... (Query principal de Cajas y filtros sin cambios) ...
        $query = Caja::query()->with(['puntoDePago.sede', 'usuario']);
        if ($this->tipo === 'dados-de-baja') {
            $query->onlyTrashed();
        }
        if (!empty($this->buscar)) {
            $termino = trim($this->buscar);
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function (Builder $q) use ($termino, $fullNameConcat, $like) {
                $q->where('nombre', $like, "%{$termino}%")
                    ->orWhereHas('puntoDePago', function ($qP) use ($termino, $like) {
                        $qP->where('nombre', $like, "%{$termino}%");
                    })
                    ->orWhereHas('usuario', function ($qU) use ($termino, $fullNameConcat, $like) {
                        $qU->where(DB::raw($fullNameConcat), $like, "%{$termino}%");
                    });
            });
            $this->tagsBusqueda[] = (object)['label' => $this->buscar, 'field' => 'buscar', 'value' => $this->buscar];
            $this->banderaFiltros = true;
        }
        if (!empty($this->filtroSede)) {
            $query->whereHas('puntoDePago', function (Builder $qPunto) {
                $qPunto->whereIn('sede_id', $this->filtroSede);
            });
        }
        if (!empty($this->filtroPuntoDePago)) {
            $query->whereIn('punto_de_pago_id', $this->filtroPuntoDePago);
            $nombresPuntos = PuntoDePago::whereIn('id', $this->filtroPuntoDePago)->pluck('nombre', 'id');
            foreach ($nombresPuntos as $id => $nombre) {
                $this->tagsBusqueda[] = (object)['label' => $nombre, 'field' => 'filtroPuntoDePago', 'value' => $id];
            }
            $this->banderaFiltros = true;
        }

        // ... (Paginación y contadores sin cambios) ...
        $cajas = $query->orderBy('id', 'desc')->paginate(12);
        $contadorTodos = Caja::count();
        $contadorBaja = Caja::onlyTrashed()->count();

        // ... (return view sin cambios) ...
        return view('livewire.puntos-de-pago.gestionar-taquillas', [
            'cajas' => $cajas,
            'sedes' => $sedes,
            'puntosDePago' => $puntosDePago,
            'usuarios' => $usuarios, // Esta variable ahora contiene solo cajeros
            'configuracion' => $configuracion,
            'contadorTodos' => $contadorTodos,
            'contadorBaja' => $contadorBaja,
            'tagsBusqueda' => $this->tagsBusqueda,
            'banderaFiltros' => $this->banderaFiltros,
            'filtrosActuales' => $this->filtrosActuales,
        ]);
    }
    public function exportarExcel()
    {
        // Recopilar filtros actuales
        $filtros = [
            'buscar' => $this->buscar,
            'filtroSede' => $this->filtroSede,
            'filtroPuntoDePago' => $this->filtroPuntoDePago,
        ];

        return Excel::download(new CajasExport($filtros, $this->tipo), 'cajas.xlsx');
    }

    /**
     * Reinicia el contador de dinero acumulado de una caja.
     */
    public function reiniciarContadorDinero($id)
    {
        try {
            $caja = Caja::findOrFail($id);
            $caja->dinero_acumulado = 0;
            $caja->save();

            $this->dispatch('notificacion', tipo: 'success', mensaje: 'Contador de dinero reiniciado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al reiniciar contador de dinero: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al reiniciar el contador.');
        }
    }
}
