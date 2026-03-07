<?php

namespace App\Livewire\Actividades;

// Importaciones de modelos necesarios
use App\Models\ActividadCategoria;
use App\Models\Sede;
use App\Models\TipoUsuario;
use App\Models\EstadoCivil;
use App\Models\TipoServicioGrupo;
use App\Models\RangoEdad;
use App\Models\Actividad;
use App\Models\PasoCrecimiento;
use \stdClass;

use Illuminate\Support\Facades\Log;

// Componentes de Livewire
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;

class CategoriasActividad extends Component
{
    public $variable = 'variable';
    // Propiedades principales de la actividad
    public $actividad;
    public $monedasActividad = [];
    public $categoriasActividad = [];
    public $categoria = [];

    // array para las opciones del select de pasos crecimiento esto funciona solo para el modal de nueva categoria

    public $arrayProcesosRequisito = [];
    public $arrayProcesosCulminar = [];
    /// estos son array para la entender mas facil el recorrido afuera en la vista
    public $pasosCrecimientoRequisito = [];
    public $pasosCrecimientoCulminar = [];

    // Propiedades para creación de nueva categoría
    public $nombreNuevo;
    public $aforoNuevo;
    public $esGratuitaNuevo = false;
    public $limiteCompras;
    public $limiteInvitados;
    // Opciones para restricciones de actividad
    public $sedes = [];
    public $tipoUsuarios = [];
    public $estadosCiviles = [];
    public $tipoServicios = [];
    public $rangosEdad = [];
    public $pasosCrecimiento = [];


    // Propiedades para nueva categoría
    public $sedesNuevo = [];
    public $tipoUsuariosNuevo = [];
    public $estadosCivilesNuevo = [];
    public $tipoServiciosNuevo = [];
    public $rangosEdadNuevo;
    public $pasoCrecimientoNuevo;
    public $pasosCrecimientoCulminarNuevo;
    public $generoNuevo = '';
    public $vinculacionGrupoNuevo = '';
    public $actividadGrupoNuevo = '';
    public $pasosCrecimientoRequisitoNuevo = '';

    // Propiedades para edición de categoría
    public $categoriaIdEditar;
    public $nombreEditar;
    public $aforoEditar;
    public $esGratuitaEditar = false;
    public $limiteComprasEditar;

    // Nuevas variables de edición (antes faltantes)
    public $sedesEditar = [];
    public $tipoUsuariosEditar = [];
    public $estadosCivilesEditar = [];
    public $tipoServiciosEditar = [];
    public $rangosEdadEditar;
    public $pasoCrecimientoEditar;
    public $pasosCrecimientoCulminarEditar;
    public $generosEditar = '';
    public $vinculacionGrupoEditar = '';
    public $actividadGrupoEditar = '';
    public $pasosCrecimientoRequisitoEditar = '';



    // Valores de monedas para edición y creación
    public $valoresMonedasEditar = [];
    public $valoresMonedasNuevo = [];
    public $actividadActual = [];


    /**
     * Método de inicialización del componente
     * Carga datos iniciales y prepara el estado del componente
     */
    public function mount()
    {

        // Cargar monedas y categorías de la actividad
        $this->monedasActividad = $this->actividad->monedas;
        $this->categoriasActividad = $this->actividad->categorias()->orderBy('id', 'asc')->get();
        $this->actividadActual = Actividad::find($this->actividad->id);
        $this->limiteInvitados = 1;

        // Cargar opciones para restricciones
        $this->sedes = Sede::get();
        $this->tipoUsuarios = TipoUsuario::get();
        $this->estadosCiviles = EstadoCivil::all();
        $this->tipoServicios = TipoServicioGrupo::all();
        $this->rangosEdad = RangoEdad::all();

        $this->pasosCrecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();

        /// aqui se crean las opciones para los select del modal de nuevo
        $contador_ids = 1;
        foreach ($this->pasosCrecimiento  as $paso_crecimiento) {

            $item = new stdClass();
            $item->id = $contador_ids++;
            $item->id_paso = $paso_crecimiento->id;
            $item->nombre = $paso_crecimiento->nombre . ' - No Realizado';
            $item->estado = 1;
            $item->indice = $item->id;
            array_push($this->arrayProcesosRequisito, $item);
            array_push($this->arrayProcesosCulminar, $item);

            $item = new stdClass();
            $item->id = $contador_ids++;
            $item->id_paso = $paso_crecimiento->id;
            $item->nombre = $paso_crecimiento->nombre . ' - En Curso';
            $item->estado = 2;
            $item->indice = $item->id;
            array_push($this->arrayProcesosRequisito, $item);
            array_push($this->arrayProcesosCulminar, $item);

            $item = new stdClass();
            $item->id = $contador_ids++;
            $item->id_paso = $paso_crecimiento->id;
            $item->nombre = $paso_crecimiento->nombre . ' - Realizado';
            $item->estado = 3;
            $item->indice = $item->id;
            array_push($this->arrayProcesosRequisito, $item);
            array_push($this->arrayProcesosCulminar, $item);
        }

        $this->pasosCrecimientoRequisito = $this->arrayProcesosRequisito;
        $this->pasosCrecimientoCulminar = $this->arrayProcesosCulminar;

        // Resetear propiedades de edición para evitar datos residuales
        $this->reset(['nombreEditar', 'aforoEditar', 'esGratuitaEditar', 'limiteComprasEditar', 'sedesEditar', 'tipoUsuariosEditar', 'estadosCivilesEditar', 'tipoServiciosEditar', 'rangosEdadEditar', 'pasoCrecimientoEditar', 'pasosCrecimientoCulminarEditar', 'generosEditar', 'vinculacionGrupoEditar', 'actividadGrupoEditar', 'pasosCrecimientoRequisitoEditar', 'categoriaIdEditar']);
    }

    /**
     * --- MÉTODO DE REGLAS CORREGIDO ---
     * Las reglas ahora son más robustas y se aplican condicionalmente.
     */
    public function rules()
    {
        $rules = [
            'nombreNuevo' => ['required', 'string'],
            'aforoNuevo' => ['required', 'integer', 'min:0'],
            'limiteCompras' => ['required', 'integer', 'min:1'],
        ];

        // Si la restricción es por categoría, se añaden más reglas obligatorias.
        if ($this->actividadActual->restriccion_por_categoria) {
            $rules['generoNuevo'] = ['required', 'not_in:0'];
            $rules['vinculacionGrupoNuevo'] = ['required', 'not_in:0'];
            $rules['actividadGrupoNuevo'] = ['required', 'not_in:0'];
            $rules['sedesNuevo'] = ['required', 'array', 'min:1'];
        }

        // Las reglas para los valores de moneda solo se aplican si la categoría NO es gratuita.
        if (!$this->esGratuitaNuevo) {
            foreach ($this->monedasActividad as $moneda) {
                $rules["valoresMonedasNuevo.{$moneda->id}"] = ['required', 'numeric', 'min:0'];
            }
        }

        return $rules;
    }

    /**
     * --- MÉTODO DE MENSAJES CORREGIDO ---
     * Se ajustan los mensajes para que coincidan con las nuevas reglas.
     */
    public function messages()
    {
        $messages = [
            'nombreNuevo.required' => 'El nombre de la categoría es obligatorio.',
            'aforoNuevo.required' => 'El aforo es obligatorio.',
            'limiteCompras.required' => 'El límite de compras es obligatorio.',
            'generoNuevo.required' => 'El género es obligatorio.',
            'vinculacionGrupoNuevo.required' => 'La vinculación a grupo es obligatoria.',
            'actividadGrupoNuevo.required' => 'La actividad en grupo es obligatoria.',
            'sedesNuevo.required' => 'Debes seleccionar al menos una sede.',
        ];

        if (!$this->esGratuitaNuevo) {
            foreach ($this->monedasActividad as $moneda) {
                $messages["valoresMonedasNuevo.{$moneda->id}.required"] = "El valor para {$moneda->nombre} es obligatorio.";
            }
        }
        return $messages;
    }

    /**
     * --- MÉTODO DE CREACIÓN RECONSTRUIDO ---
     * Ahora siempre valida y maneja el caso de categorías gratuitas sin monedas.
     */
    public function nuevaCategoria()
    {


        // aqui primero creo la actividad categoria para poder luego crear los registros de las tablas intermedias
        $categoriaActividad = new ActividadCategoria();
        $categoriaActividad->actividad_id = $this->actividad->id;
        $categoriaActividad->nombre = $this->nombreNuevo;
        $categoriaActividad->aforo = $this->aforoNuevo;
        $categoriaActividad->es_gratuita = $this->esGratuitaNuevo;
        $categoriaActividad->limite_invitados = $this->limiteInvitados;


        $categoriaActividad->save();

        // Manejo de monedas
        if (!$this->esGratuitaNuevo) {
            $categoriaActividad->limite_compras = $this->limiteCompras;

            $categoriaActividad->genero = $this->generoNuevo;
            $categoriaActividad->vinculacion_grupo = $this->vinculacionGrupoNuevo;
            $categoriaActividad->actividad_grupo = $this->actividadGrupoNuevo;
            $categoriaActividad->sedes()->sync($this->sedesNuevo);
            $categoriaActividad->save();

            $monedasParaSync = [];
            foreach ($this->monedasActividad as $moneda) {
                $valor = $this->valoresMonedasNuevo[$moneda->id] ?? null;
                if ($valor !== null) {
                    $monedasParaSync[$moneda->id] = ['valor' => $valor];
                }
            }
            $categoriaActividad->monedas()->sync($monedasParaSync);


            // aqui se hace el guardado de los pasos crecimiento requisito de la categoria
            if (isset($this->pasosCrecimientoRequisitoNuevo)) {
                $contador_ids = 1;
                $array_procesos = array();

                foreach ($this->pasosCrecimiento  as $paso_crecimiento) {

                    $item = new stdClass();
                    $item->id = $contador_ids;
                    $item->id_paso = $paso_crecimiento->id;
                    $item->estado = 1;
                    $item->indice = $contador_ids;
                    $array_procesos[] = $item;
                    $contador_ids = $contador_ids + 1;

                    $item = new stdClass();
                    $item->id = $contador_ids;
                    $item->id_paso = $paso_crecimiento->id;
                    $item->estado = 2;
                    $item->indice = $contador_ids;
                    $array_procesos[] = $item;
                    $contador_ids = $contador_ids + 1;

                    $item = new stdClass();
                    $item->id = $contador_ids;
                    $item->id_paso = $paso_crecimiento->id;
                    $item->estado = 3;
                    $item->indice = $contador_ids;
                    $array_procesos[] = $item;
                    $contador_ids = $contador_ids + 1;
                }

                $collection = collect($array_procesos)->whereIn('id', $this->pasosCrecimientoRequisitoNuevo)->keyBy('id_paso')->select('estado', 'indice');

                $categoriaActividad->procesosRequisito()->sync($collection);
            }
            // aqui se hace el guardado de los pasos crecimiento requisito de la categoria
            if (isset($this->pasosCrecimientoCulminarNuevo)) {
                $contador_ids = 1;
                $array_procesos = array();

                foreach ($this->pasosCrecimiento  as $paso_crecimiento) {

                    $item = new stdClass();
                    $item->id = $contador_ids;
                    $item->id_paso = $paso_crecimiento->id;
                    $item->estado = 1;
                    $item->indice = $contador_ids;
                    $array_procesos[] = $item;
                    $contador_ids = $contador_ids + 1;

                    $item = new stdClass();
                    $item->id = $contador_ids;
                    $item->id_paso = $paso_crecimiento->id;
                    $item->estado = 2;
                    $item->indice = $contador_ids;
                    $array_procesos[] = $item;
                    $contador_ids = $contador_ids + 1;

                    $item = new stdClass();
                    $item->id = $contador_ids;
                    $item->id_paso = $paso_crecimiento->id;
                    $item->estado = 3;
                    $item->indice = $contador_ids;
                    $array_procesos[] = $item;
                    $contador_ids = $contador_ids + 1;
                }

                // return $request->pasosCrecimientoRequisito;
                $collection = collect($array_procesos)->whereIn('id', $this->pasosCrecimientoCulminarNuevo)->keyBy('id_paso')->select('estado', 'indice');
                //return $collection->toArray();
                $categoriaActividad->procesosCulminados()->sync($collection);
            }

            /// estos son los restantes campos de restricciones que no tienen guardados especiales
            if (isset($this->rangosEdadNuevo)) {
                $categoriaActividad->rangosEdad()->sync($this->rangosEdadNuevo);
            }

            if (isset($this->tipoUsuariosNuevo)) {
                $categoriaActividad->tipoUsuarios()->sync($this->tipoUsuariosNuevo);
            }

            if (isset($this->tipoServiciosNuevo)) {
                $categoriaActividad->tipoServicios()->sync($this->tipoServiciosNuevo);
            }
        }

        if ($this->esGratuitaNuevo == '') {
            $this->reset([
                'nombreNuevo',
                'aforoNuevo',
                'esGratuitaNuevo',
                'generoNuevo',
                'sedesNuevo',
                'pasosCrecimientoCulminarNuevo',
                'rangosEdadNuevo',
                'pasosCrecimientoRequisitoNuevo',
                'tipoUsuariosNuevo',
                'estadosCivilesNuevo',
                'tipoServiciosNuevo',

            ]);
        }


        return redirect()->route('actividades.categorias', [$this->actividad])->with('success', "Tu actividad: <b>" . $categoriaActividad->nombre . "</b> fue actualizada con éxito.");
    }

    #[On('abrir-modal-actualizar-categoria')]
    public function abrirModalActualizarCategoria($categoriaId)
    {
        $categoria = ActividadCategoria::with([
            'sedes',
            'monedas',
            'rangosEdad',
            'tipoUsuarios',
            'tipoServicios',
            'estadosCiviles',
            'procesosRequisito',
            'procesosCulminados'
        ])->find($categoriaId);

        // Asignar valores
        $this->categoriaIdEditar = $categoriaId;
        $this->nombreEditar = $categoria->nombre;
        $this->aforoEditar = $categoria->aforo;
        $this->esGratuitaEditar = $categoria->es_gratuita;
        $this->generosEditar = $categoria->genero; // Asegúrate de que este campo esté correctamente asignado
        $this->vinculacionGrupoEditar = $categoria->vinculacion_grupo; // Asegúrate de que este campo esté correctamente asignado
        $this->actividadGrupoEditar = $categoria->actividad_grupo; // Asegúrate de que este campo esté correctamente asignado
        $this->limiteComprasEditar = $categoria->limite_compras;

        // Cargar relaciones
        $this->sedesEditar = $categoria->sedes->pluck('id')->toArray();
        $this->rangosEdadEditar = $categoria->rangosEdad->pluck('id')->toArray();
        $this->tipoUsuariosEditar = $categoria->tipoUsuarios->pluck('id')->toArray();
        $this->tipoServiciosEditar = $categoria->tipoServicios->pluck('id')->toArray();
        $this->estadosCivilesEditar = $categoria->estadosCiviles->pluck('id')->toArray();

        // Cargar monedas si no es gratuita
        if (!$categoria->es_gratuita) {
            $this->valoresMonedasEditar = $categoria->monedas
                ->pluck('pivot.valor', 'id')
                ->toArray();
        }

        // Cargar procesos
        $this->pasosCrecimientoRequisitoEditar = $categoria->procesosRequisito
            ->pluck('pivot.indice')
            ->toArray();
        $this->pasosCrecimientoCulminarEditar = $categoria->procesosCulminados
            ->pluck('pivot.indice')
            ->toArray();

        // Disparar evento para abrir el modal
        $this->dispatch('abrirModal', nombreModal: 'modalEditarCategoria');
    }
    public function confirmarEliminarCategoria($categoriaId)
    {
        $this->dispatch('confirmarEliminarCategoria', categoriaId: $categoriaId);
    }

    public function eliminarCategoria($categoriaId)
    {
        // Buscar la categoría de actividad

        $categoriaActividad = ActividadCategoria::find($categoriaId);


        if ($categoriaActividad->aforo_ocupado > 0) {
            $this->dispatch(
                'msn',
                msnIcono: 'alert',
                msnTitulo: '¡Opps!',
                msnTexto: 'La categoría no se puede eliminar, ya existen registros de compras para esta categoría.'
            );
        } else {

            // Eliminar relaciones en tablas intermedias
            $categoriaActividad->sedes()->detach();
            $categoriaActividad->monedas()->detach();
            $categoriaActividad->procesosRequisito()->detach();
            $categoriaActividad->procesosCulminados()->detach();
            $categoriaActividad->rangosEdad()->detach();
            $categoriaActividad->tipoUsuarios()->detach();
            $categoriaActividad->tipoServicios()->detach();

            // Eliminar la categoría de actividad
            $categoriaActividad->delete();

            // Disparar un evento de notificación
            $this->dispatch(
                'msn',
                msnIcono: 'success',
                msnTitulo: '¡Muy bien!',
                msnTexto: 'La categoría fue eliminada con éxito.'
            );
        }

        // Opcional: Recargar la página o actualizar la vista
        $this->mount();
    }


    /**
     * Método para actualizar una categoría existente
     */
    public function actualizarCategoria()
    {
        // Validar los datos
        $this->validate([
            'nombreEditar' => 'required|string',
            // ... (otras reglas de validación)
        ]);

        // Obtener la categoría a actualizar
        $categoriaActividad = ActividadCategoria::find($this->categoriaIdEditar);

        // Actualizar campos básicos
        $categoriaActividad->nombre = $this->nombreEditar;
        $categoriaActividad->es_gratuita = $this->esGratuitaEditar;
        $categoriaActividad->save();

        // Manejo de monedas si no es gratuita
        if (!$this->esGratuitaEditar) {
            $monedasParaSync = [];
            foreach ($this->monedasActividad as $moneda) {
                $valor = $this->valoresMonedasEditar[$moneda->id] ?? null;
                if ($valor !== null) {
                    $monedasParaSync[$moneda->id] = ['valor' => $valor];
                }
            }
            $categoriaActividad->monedas()->sync($monedasParaSync);
        } else {
            // Si es gratuita, eliminar todas las monedas asociadas
            $categoriaActividad->monedas()->detach();
        }

        $this->mount();

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'La categoría se editó con éxito.'
        );

        $this->dispatch('cerrarModal', nombreModal: 'modalEditarCategoria');
    }

    /**
     * Método de renderizado del componente
     * Actualiza las monedas y categorías antes de renderizar
     */
    public function render()
    {

        // Para depuración, puedes agregar un log aquí
        $this->actividadActual;

        $this->monedasActividad = $this->actividad->monedas;
        $this->categoriasActividad = $this->actividad->categorias()->orderBy('id', 'asc')->get();

        return view('livewire.actividades.categorias-actividad');
    }
}
