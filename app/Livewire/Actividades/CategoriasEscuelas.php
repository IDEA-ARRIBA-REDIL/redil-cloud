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
use App\Models\Periodo;
use App\Models\PasoCrecimiento;
use \stdClass;

use Illuminate\Support\Facades\Log;

// Componentes de Livewire
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;

class CategoriasEscuelas extends Component
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
    public $limiteCompras = 1;
    public $periodo;
    public $materiasPeriodo = [];
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
    public $materiaPeriodo;

    // Propiedades para edición de categoría
    public $categoriaIdEditar;
    public $nombreEditar;
    public $aforoEditar;
    public $esGratuitaEditar = false;
    public $limiteComprasEditar;
    public $materiaPeriodoEditar;





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
        $this->periodo = Periodo::find($this->actividad->periodo_id);


        if (isset($this->periodo->materiasPeriodo)) {
            $this->materiasPeriodo = $this->periodo->materiasPeriodo;
        } else {
            $this->materiasPeriodo = [];
        }

        // $pasos_crecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();

        /// yo no sabia pero se debe mandar el valor de las variables de las categorias vacias para el mount
        /// porque en el actualizar las voy a necesitar iniciadas para que los value funcionen.

        $this->nombreEditar = '';
        $this->aforoEditar = '';
        $this->esGratuitaEditar = false;
        $this->limiteCompras = 1;
        $this->materiaPeriodo;
        $this->materiaPeriodoEditar;
    }
    /// metodo para validar
    public function rules()
    {
        $rules = [
            // Valores de monedas (solo si no es gratuita)
            'valoresMonedasNuevo' => ['array'],
            // Nombre categoria
            'nombreNuevo' => [
                'required',
                'string',
            ],


        ];

        // Añadir reglas de validación específicas para cada moneda si no es gratuito
        if (!$this->esGratuitaNuevo) {
            foreach ($this->monedasActividad as $moneda) {
                $rules["valoresMonedasNuevo.{$moneda->id}"] = [
                    'required',
                    'numeric',
                    'min:0'
                ];
            }
        }

        return $rules;
    }
    /// metodo para los mensajes de error personalizados
    public function messages()
    {
        $messages = [
            'nombreNuevo.required' => 'Por favor, ingrese el nombre de la actividad.',
            'nombreNuevo.string' => 'El nombre de la actividad debe contener caracteres válidos.',

        ];

        if (!$this->esGratuitaNuevo) {
            foreach ($this->monedasActividad as $moneda) {
                $messages["valoresMonedasNuevo.{$moneda->id}.required"] =
                    "El valor para {$moneda->nombre} es obligatorio.";
                $messages["valoresMonedasNuevo.{$moneda->id}.numeric"] =
                    "El valor para {$moneda->nombre} debe ser un número.";
                $messages["valoresMonedasNuevo.{$moneda->id}.min"] =
                    "El valor para {$moneda->nombre} no puede ser negativo.";
            }
        }

        return $messages;
    }

    public function nuevaCategoria()
    {
        // esta funcion ejecuta la accion de rules
        if ($this->esGratuitaNuevo == '') {
            $validatedData = $this->validate();
        }



        // aqui primero creo la actividad categoria para poder luego crear los registros de las tablas intermedias

        $categoriaActividad = new ActividadCategoria();
        $categoriaActividad->actividad_id = $this->actividad->id;
        $categoriaActividad->nombre = $this->nombreNuevo;
        $categoriaActividad->aforo = $this->aforoNuevo;
        $categoriaActividad->es_gratuita = $this->esGratuitaNuevo;
        $categoriaActividad->limite_compras = $this->limiteCompras;
        $categoriaActividad->materia_periodo_id = $this->materiaPeriodo;

        $categoriaActividad->save();

        /// crear categorias actividad monedas

        $monedasParaSync = [];
        foreach ($this->monedasActividad as $moneda) {
            $valor = $this->valoresMonedasNuevo[$moneda->id] ?? null;
            if ($valor !== null) {
                $monedasParaSync[$moneda->id] = ['valor' => $valor];
            }
        }
        $categoriaActividad->monedas()->sync($monedasParaSync);



        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'La categoría fue creada con exito.'
        );


        $this->dispatch('cerrarModal', nombreModal: 'modalNuevaCategoria');

        $this->mount();
        //return redirect()->route('actividades.categorias', [$this->actividad])->with('success', "Tu actividad: <b>" . $categoriaActividad->nombre . "</b> fue actualizada con éxito.");

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
        $this->esGratuitaEditar = $categoria->es_gratuita;
        $this->limiteComprasEditar = $categoria->limite_compras;
        $this->materiaPeriodoEditar = $categoria->materia_periodo_id;

        // Cargar monedas si no es gratuita
        if (!$categoria->es_gratuita) {
            $this->valoresMonedasEditar = $categoria->monedas
                ->pluck('pivot.valor', 'id')
                ->toArray();
        }


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
    }



    /**
     * Método para actualizar una categoría existente
     */
    public function actualizarCategoria()
    {
        // Validar los datos

        // Obtener la categoría a actualizar
        $categoriaActividad = ActividadCategoria::find($this->categoriaIdEditar);
        $this->variable = $this->materiaPeriodoEditar;

        // Actualizar campos básicos

        $categoriaActividad->nombre = $this->nombreEditar;
        $categoriaActividad->es_gratuita = $this->esGratuitaEditar;
        $categoriaActividad->limite_compras = $this->limiteComprasEditar;
        $categoriaActividad->materia_periodo_id = $this->materiaPeriodoEditar;
        $$categoriaActividad->save();



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

        $this->reset([
            'nombreEditar',
            'esGratuitaEditar',


        ]);

        $this->mount();

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'La categoría se editó con exito.'
        );


        $this->dispatch('cerrarModal', nombreModal: 'modalEditarCategoria');



        // Redirigir con mensaje de éxito

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

        return view('livewire.actividades.categorias-escuelas');
    }
}
