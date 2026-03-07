<?php

namespace App\Livewire\PuntosDePago;

// --- Importaciones de Clases ---
use Livewire\Component;
use Livewire\WithPagination;      // Para la paginación de Livewire
use Livewire\Attributes\On;        // Para los listeners de eventos (Livewire 3)
use App\Models\PuntoDePago;
use App\Models\Sede;
use App\Models\User;             // Para los Encargados
use App\Models\Configuracion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log; // Para registrar errores
use Illuminate\Support\Facades\DB;  // Para usar DB::raw()
use App\Models\AsesorPdp;
use Illuminate\Http\Request;
use App\Exports\PuntosDePagoExport;
use Maatwebsite\Excel\Facades\Excel;

class GestionarPuntosDePago extends Component
{
    // Habilita la paginación
    use WithPagination;

     protected $paginationTheme = 'bootstrap';

    // =================================================================
    // PROPIEDADES PÚBLICAS (El "estado" del componente)
    // =================================================================

    // --- Propiedades para Filtros y Búsqueda ---
    public $tipo = 'todos'; // Valor inicial: 'todos' o 'dados-de-baja'
    public $buscar = ''; // Para el input de búsqueda
    public $filtroSede = []; // Para el <select> de sedes

    // --- Propiedades para el Modal de PUNTO DE PAGO ---
    public $modalPuntoDePagoAbierto = false; // Controla si el modal está visible
    public $esEdicion = false; // Define si el modal está en modo "Crear" o "Editar"
    public $puntoDePagoId;
    public $nombrePunto;
    public $sedeId;
    public $encargadoId; // user_id del Encargado del Punto de Pago
    public $tagsBusqueda = [];
    public $banderaFiltros = false;
    public $filtrosActuales = []; // Para pasar los valores al formulario del Offcanvas


    // =================================================================
    // MÉTODOS DEL CICLO DE VIDA
    // =================================================================

    /**
     * Método 'mount'
     */
    public function mount(Request $request, $tipo = 'todos')
    {
        $this->tipo = $tipo;

        // Llenar los filtros desde la URL
        $this->filtrosActuales = [
            'buscar' => $request->query('buscar', ''),
            'filtroSede' => $request->query('filtroSede', []),
        ];

        // Asignar a las propiedades públicas que usa el render()
        $this->buscar = $this->filtrosActuales['buscar'];
        $this->filtroSede = $this->filtrosActuales['filtroSede'];
    }

    /**
     * Método 'updated'
     * Resetea la paginación si el usuario cambia un filtro.
     */
    public function updated($propiedad)
    {
        if (in_array($propiedad, ['buscar'])) {
            // El 'buscar' sí sigue siendo "live"
            $this->resetPage();
        }
    }

    // =================================================================
    // MÉTODOS DEL CRUD DE PUNTOS DE PAGO
    // =================================================================

    /**
     * Resetea el formulario del modal de Punto de Pago.
     */
    private function resetearFormularioPunto()
    {
        $this->reset(['esEdicion', 'puntoDePagoId', 'nombrePunto', 'sedeId', 'encargadoId']);
        $this->resetErrorBag(); // Limpia los errores de validación
    }

    /**
     * Abre el modal para CREAR un nuevo punto de pago.
     */
    public function abrirModalCrearPunto()
    {
        $this->resetearFormularioPunto();
        $this->esEdicion = false;
        // Dispara un evento JS para que Bootstrap muestre el modal
        $this->dispatch('abrir-modal-punto');
    }

    /**
     * Abre el modal para EDITAR un punto de pago existente.
     */
    public function abrirModalEditarPunto(PuntoDePago $puntoDePago)
    {
        $this->resetearFormularioPunto();
        $this->esEdicion = true;

        // Cargamos los datos del punto de pago en las propiedades públicas
        $this->puntoDePagoId = $puntoDePago->id;
        $this->nombrePunto = $puntoDePago->nombre;
        $this->sedeId = $puntoDePago->sede_id;
        $this->encargadoId = $puntoDePago->encargado_id;

        // Dispara un evento JS para que Bootstrap muestre el modal
        $this->dispatch('abrir-modal-punto');
    }

    /**
     * Cierra el modal de Punto de Pago.
     */
    public function cerrarModalPuntoDePago()
    {
        $this->dispatch('cerrar-modal-punto');
        $this->resetearFormularioPunto();
    }

    /**
     * Guarda (Crea o Actualiza) un Punto de Pago.
     * ¡MODIFICADO SEGÚN TU SOLICITUD!
     */
    public function guardarPuntoDePago()
    {
        // Validación de datos (sigue siendo la misma)
        $this->validate([
            'nombrePunto' => 'required|string|max:100',
            'sedeId' => 'required|integer|exists:sedes,id',
            'encargadoId' => 'nullable|integer|exists:users,id',
        ], [
            'nombrePunto.required' => 'El nombre es obligatorio.',
            'sedeId.required' => 'Debe seleccionar una sede.',
        ]);

        try {
            $mensaje = ''; // Variable para el mensaje de notificación

            if ($this->esEdicion) {
                // --- LÓGICA DE ACTUALIZACIÓN (Find and Save) ---
                $punto = PuntoDePago::findOrFail($this->puntoDePagoId);
                $punto->nombre = $this->nombrePunto;
                $punto->sede_id = $this->sedeId;
                $punto->encargado_id = $this->encargadoId ?: null;
                $punto->save();

                $mensaje = 'Punto de pago actualizado.';
            } else {
                // --- LÓGICA DE CREACIÓN (Create) ---
                PuntoDePago::create([
                    'nombre' => $this->nombrePunto,
                    'sede_id' => $this->sedeId,
                    'encargado_id' => $this->encargadoId ?: null,
                ]);

                $mensaje = 'Punto de pago creado.';
            }
            $titulo = 'Felicitaciones';

            // Dispara una notificación (Toast)
            $this->dispatch('notificacion', tipo: 'success', mensaje: $mensaje, titulo: $titulo);

            $this->cerrarModalPuntoDePago(); // Cierra el modal al guardar

        } catch (\Exception $e) {
            $titulo = 'Error';
            Log::error('Error al guardar punto de pago: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Ocurrió un error al guardar.', titulo: $titulo);
        }
    }

    /**
     * Muestra una alerta de confirmación (SweetAlert) antes de eliminar.
     * ¡MODIFICADO!
     */
    public function confirmarEliminacionPunto($id)
    {
        $this->dispatch(
            'confirmarEliminacion',
            titulo: '¿Dar de baja el Punto de Pago?',
            texto: 'Esta acción dará de baja el punto de pago. Se puede revertir más tarde.', // Texto actualizado
            evento: 'eliminarPuntoDePagoConfirmado', // Evento que se disparará si confirma
            id: $id
        );
    }

    /**
     * Elimina (Soft Delete) el punto de pago.
     * ¡MODIFICADO!
     */
    #[On('eliminarPuntoDePagoConfirmado')] // Atributo de Listener (Livewire 3)
    public function eliminarPuntoDePago($id)
    {
        try {
            $punto = PuntoDePago::findOrFail($id);
            $punto->delete(); // Soft delete

            // ¡Lógica de eliminar cajas ha sido removida!

            $this->dispatch('notificacion', tipo: 'success', mensaje: 'Punto de pago dado de baja.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar punto de pago: ' . $e->getMessage());
            $this->dispatch('notificacion',  tipo: 'error', mensaje: 'Error al dar de baja.');
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de un punto de pago.
     * Es un "toggle" que invierte el valor actual.
     */
    public function toggleEstado($id)
    {
        try {
            // Buscamos el punto de pago (sin incluir los dados de baja)
            $punto = PuntoDePago::findOrFail($id);

            // Invertimos el estado (si es true, lo vuelve false y viceversa)
            $punto->estado = !$punto->estado;
            $punto->save();

            // Preparamos el mensaje para la notificación
            $nuevoEstado = $punto->estado ? 'activado' : 'inactivado';
            $this->dispatch(
                'notificacion',
                tipo: 'success',
                mensaje: "Punto de pago {$nuevoEstado}."
            );
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de PuntoDePago: ' . $e->getMessage());
            $this->dispatch(
                'notificacion',
                tipo: 'error',
                mensaje: 'Error al cambiar el estado.'
            );
        }
    }


    /**
     * Renderiza la vista del componente y pasa los datos.
     * ¡MODIFICADO!
     */
    public function render()
    {
        // --- 0. Resetear tags de filtros ---
        $this->tagsBusqueda = [];
        $this->banderaFiltros = false;

        // --- 1. Obtener datos para filtros y modales ---
        $sedes = Sede::select('id', 'nombre')->orderBy('nombre')->get();

        $fullNameConcat = "CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido, users.segundo_apellido)";
        $listaEncargados = User::select('users.id', DB::raw("($fullNameConcat) as name"))
            ->join('asesores_pdp', 'users.id', '=', 'asesores_pdp.user_id')
            ->where('asesores_pdp.es_encargado', true)
            ->where('asesores_pdp.activo', true)
            ->orderBy('primer_nombre')
            ->get();

        $configuracion = Configuracion::first();

        // --- 2. Construir la consulta principal (Query) ---
        $query = PuntoDePago::query()->with(['sede', 'encargado']);

        // Verificación de Permiso para listar todos los PDP (Usando Rol Activo)
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        
        if (!$rolActivo || !$rolActivo->hasPermissionTo('pdp.opcion_listar_todos_los_pdp')) {
            // Si no tiene permiso en su rol activo, solo ve los que encarga
            $query->where('encargado_id', auth()->id());
        }

        if ($this->tipo === 'dados-de-baja') {
            $query->onlyTrashed();
        }

        // Aplicar filtro de búsqueda por palabra
        if (!empty($this->buscar)) {
            $termino = trim($this->buscar);
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function (Builder $q) use ($termino, $fullNameConcat, $like) {
                $q->where('nombre', $like, "%{$termino}%")
                    ->orWhereHas('sede', function ($qSede) use ($termino, $like) {
                        $qSede->where('nombre', $like, "%{$termino}%");
                    })
                    ->orWhereHas('encargado', function ($qUser) use ($termino, $fullNameConcat, $like) {
                        $qUser->where(DB::raw($fullNameConcat), $like, "%{$termino}%");
                    });
            });

            // --- Lógica para crear Tag de Búsqueda ---
            $this->tagsBusqueda[] = (object)['label' => $this->buscar, 'field' => 'buscar', 'value' => $this->buscar];
            $this->banderaFiltros = true;
        }

        // Aplicar filtro de Sede
        if (!empty($this->filtroSede)) {
            $query->whereIn('sede_id', $this->filtroSede);

            // --- Lógica para crear Tags de Sede ---
            $nombresSedes = Sede::whereIn('id', $this->filtroSede)->pluck('nombre', 'id');
            foreach ($nombresSedes as $id => $nombre) {
                $this->tagsBusqueda[] = (object)['label' => $nombre, 'field' => 'filtroSede', 'value' => $id];
            }
            $this->banderaFiltros = true;
        }

        // --- 3. Obtener resultados y contadores ---
        $puntosDePago = $query->orderBy('id', 'desc')->paginate(12);
        $contadorTodos = PuntoDePago::count();
        $contadorBaja = PuntoDePago::onlyTrashed()->count();

        // --- 4. Retornar la vista y pasarle los datos ---
        return view('livewire.puntos-de-pago.gestionar-puntos-de-pago', [
            'puntosDePago' => $puntosDePago,
            'sedes' => $sedes,
            'listaEncargados' => $listaEncargados,
            'configuracion' => $configuracion,
            'contadorTodos' => $contadorTodos,
            'contadorBaja' => $contadorBaja,
            // ¡NUEVAS VARIABLES PARA LA VISTA!
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
        ];

        return Excel::download(new PuntosDePagoExport($filtros, $this->tipo), 'puntos_de_pago.xlsx');
    }
}
