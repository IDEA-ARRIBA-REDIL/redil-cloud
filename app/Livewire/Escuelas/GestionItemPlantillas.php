<?php

namespace App\Livewire\Escuelas; // Namespace confirmado por el usuario

use Livewire\Component;
use App\Models\Materia;
use App\Models\ItemPlantilla;
use App\Models\CorteEscuela;
use App\Models\TipoItem;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para la transacción de duplicación

class GestionItemPlantillas extends Component
{
    public Materia $materia;
    public $itemPlantillas = [];
    public $cortesEscuela = [];
    public $tiposItem = [];

    // --- Propiedades para el formulario CREAR ---
    public $nombreCrear;
    public $corte_escuela_idCrear;
    public $tipo_item_idCrear;
    public $contenidoCrear;
    public $visible_predeterminadoCrear = true;
    public $entregable_predeterminadoCrear = false;
    public $porcentaje_sugeridoCrear;
    public $ordenCrear;

    // --- Propiedades para el formulario EDITAR ---
    public $itemIdEditar = null; // ID del item a editar
    public $nombreEditar;
    public $corte_escuela_idEditar;
    public $tipo_item_idEditar;
    public $contenidoEditar;
    public $visible_predeterminadoEditar = true;
    public $entregable_predeterminadoEditar = false;
    public $porcentaje_sugeridoEditar;
    public $ordenEditar;

    // --- Propiedades para el MODAL DUPLICAR MODELO ---
    public $materiasParaDuplicar = [];
    public $materiaIdFuenteParaDuplicar = null; // ID de la materia desde donde se copiarán los ítems

    /**
     * Define las reglas de validación dinámicamente.
     *
     * @param string $mode Puede ser 'crear', 'editar', o 'duplicar'.
     * @return array
     */
    protected function rules($mode = 'crear')
    {
        $prefix = $mode === 'editar' ? 'Editar' : ($mode === 'crear' ? 'Crear' : '');
        $rules = [];

        if ($mode === 'crear' || $mode === 'editar') {
            $rules = [
                'nombre'.$prefix => 'required|string|max:255',
                'corte_escuela_id'.$prefix => [
                    'required',
                    'integer',
                    Rule::exists('cortes_escuela', 'id')->where(function ($query) {
                        $query->where('escuela_id', $this->materia->escuela_id);
                    }),
                ],
                'tipo_item_id'.$prefix => 'nullable|integer|exists:tipos_item,id',
                'contenido'.$prefix => 'nullable|string',
                'visible_predeterminado'.$prefix => 'required|boolean',
                'entregable_predeterminado'.$prefix => 'required|boolean',
                'porcentaje_sugerido'.$prefix => 'nullable|numeric|min:0|max:100',
                'orden'.$prefix => 'required|integer|min:0',
            ];
        } elseif ($mode === 'duplicar') {
            $rules = [
                'materiaIdFuenteParaDuplicar' => [
                    'required',
                    'integer',
                    Rule::exists('materias', 'id')->where(function ($query) {
                        // Asegurar que la materia fuente pertenezca a la misma escuela y no sea la materia actual
                        $query->where('escuela_id', $this->materia->escuela_id)
                              ->where('id', '!=', $this->materia->id);
                    }),
                ],
            ];
        }
        return $rules;
    }

    /**
     * Mensajes de validación personalizados.
     *
     * @var array
     */
    protected $messages = [
        'required' => 'El campo es obligatorio.',
        '*.required' => 'El campo es obligatorio.', // Mensaje genérico para cualquier campo requerido
        'corte_escuela_idCrear.exists' => 'El corte seleccionado no es válido para esta escuela.',
        'corte_escuela_idEditar.exists' => 'El corte seleccionado no es válido para esta escuela.',
        'materiaIdFuenteParaDuplicar.required' => 'Debes seleccionar una materia de origen.',
        'materiaIdFuenteParaDuplicar.exists' => 'La materia de origen seleccionada no es válida o no pertenece a la misma escuela.',
        'integer' => 'El valor debe ser un número entero.',
        'numeric' => 'El valor debe ser numérico.',
        'boolean' => 'El valor debe ser verdadero o falso.',
        'string' => 'El valor debe ser texto.',
        'max' => 'El campo no debe exceder :max caracteres.',
        'min' => 'El valor debe ser al menos :min.',
    ];

    /**
     * Se ejecuta cuando el componente se inicializa.
     * Carga la materia y los datos iniciales.
     *
     * @param Materia $materia La instancia de la materia actual.
     */
    public function mount(Materia $materia)
    {
        $this->materia = $materia;
        $this->cargarDatos();
    }

    /**
     * Carga los datos necesarios para el componente (ítems, cortes, tipos).
     */
    public function cargarDatos()
    {
        $this->itemPlantillas = $this->materia->itemPlantillas()
                                    ->with(['corteEscuela', 'tipoItem']) // Carga ansiosa de relaciones
                                    ->orderBy('corte_escuela_id') // Ordenar por corte
                                    ->orderBy('orden') // Luego por orden dentro del corte
                                    ->get();

        // Cargar cortes de la escuela de la materia actual
        $this->cortesEscuela = CorteEscuela::where('escuela_id', $this->materia->escuela_id)
                                    ->orderBy('orden')
                                    ->get();

        // Cargar todos los tipos de ítem disponibles
        $this->tiposItem = TipoItem::orderBy('nombre')->get();
    }

    // --- Métodos para Offcanvas CREAR Ítem ---

    /**
     * Resetea los campos del formulario de creación.
     */
    private function resetFormularioCrear()
    {
        $this->reset([
            'nombreCrear', 'corte_escuela_idCrear', 'tipo_item_idCrear',
            'contenidoCrear', 'visible_predeterminadoCrear', 'entregable_predeterminadoCrear',
            'porcentaje_sugeridoCrear', 'ordenCrear'
        ]);
        // Restablecer valores booleanos por defecto explícitamente
        $this->visible_predeterminadoCrear = true;
        $this->entregable_predeterminadoCrear = false;
        $this->resetErrorBag(); // Limpiar errores de validación
    }

    /**
     * Prepara y abre el offcanvas para crear un nuevo ítem.
     */
    public function abrirOffcanvasCrear()
    {
        $this->resetFormularioCrear();
        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasCrearItem'); // Despacha evento para JS
    }

    /**
     * Cierra el offcanvas de creación y resetea el formulario.
     */
    public function cerrarOffcanvasCrear()
    {
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasCrearItem'); // Despacha evento para JS
        // El reseteo del formulario ahora se maneja también en el listener JS 'hidden.bs.offcanvas'
        // para cubrir cierres manuales (X, ESC), pero es bueno tenerlo aquí por si se llama directamente.
        // $this->resetFormularioCrear();
    }

    /**
     * Valida y guarda una nueva plantilla de ítem.
     */
    public function guardarItemNuevo()
    {
        $datosValidados = $this->validate($this->rules('crear'));

        // Mapear nombres de propiedades validadas a nombres de columna BD
        $datosParaGuardar = [
            'materia_id' => $this->materia->id,
            'nombre' => $datosValidados['nombreCrear'],
            'corte_escuela_id' => $datosValidados['corte_escuela_idCrear'],
            'tipo_item_id' => $datosValidados['tipo_item_idCrear'],
            'contenido' => $datosValidados['contenidoCrear'],
            'visible_predeterminado' => $datosValidados['visible_predeterminadoCrear'],
            'entregable_predeterminado' => $datosValidados['entregable_predeterminadoCrear'],
            'porcentaje_sugerido' => $datosValidados['porcentaje_sugeridoCrear'],
            'orden' => $datosValidados['ordenCrear'],
        ];

        try {
            ItemPlantilla::create($datosParaGuardar);
            session()->flash('success', 'Plantilla de ítem creada correctamente.');
            $this->cerrarOffcanvasCrear(); // Llama al método que despacha el evento JS
            $this->cargarDatos(); // Recargar la lista de ítems

        } catch (\Exception $e) {
            Log::error('Error al crear ItemPlantilla: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al crear el ítem: ' . $e->getMessage());
            // No cerrar el offcanvas en caso de error para que el usuario pueda corregir
        }
    }


    // --- Métodos para Offcanvas EDITAR Ítem ---

    /**
     * Resetea los campos del formulario de edición.
     */
    private function resetFormularioEditar()
    {
        $this->reset([
            'itemIdEditar', 'nombreEditar', 'corte_escuela_idEditar', 'tipo_item_idEditar',
            'contenidoEditar', 'visible_predeterminadoEditar', 'entregable_predeterminadoEditar',
            'porcentaje_sugeridoEditar', 'ordenEditar'
        ]);
         // Restablecer valores booleanos por defecto explícitamente
         $this->visible_predeterminadoEditar = true;
         $this->entregable_predeterminadoEditar = false;
        $this->resetErrorBag();
    }

    /**
     * Carga datos y abre el offcanvas para editar un ítem existente.
     *
     * @param int $id El ID del ItemPlantilla a editar.
     */
    public function abrirOffcanvasEditar($id)
    {
        $this->resetFormularioEditar(); // Resetear antes de cargar nuevos datos
        $item = ItemPlantilla::find($id);

        if ($item && $item->materia_id == $this->materia->id) { // Verificación de pertenencia
            $this->itemIdEditar = $item->id;
            $this->nombreEditar = $item->nombre;
            $this->corte_escuela_idEditar = $item->corte_escuela_id;
            $this->tipo_item_idEditar = $item->tipo_item_id;
            $this->contenidoEditar = $item->contenido;
            $this->visible_predeterminadoEditar = (bool) $item->visible_predeterminado; // Asegurar boolean
            $this->entregable_predeterminadoEditar = (bool) $item->entregable_predeterminado; // Asegurar boolean
            $this->porcentaje_sugeridoEditar = $item->porcentaje_sugerido;
            $this->ordenEditar = $item->orden;

            $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasEditarItem'); // Despacha evento para JS
        } else {
             session()->flash('error', 'Ítem no encontrado o no pertenece a esta materia.');
        }
    }

    /**
     * Cierra el offcanvas de edición y resetea el formulario.
     */
    public function cerrarOffcanvasEditar()
    {
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditarItem'); // Despacha evento para JS
        // $this->resetFormularioEditar();
    }

    /**
     * Valida y actualiza una plantilla de ítem existente.
     */
    public function actualizarItem()
    {
        if (!$this->itemIdEditar) {
             session()->flash('error', 'No se ha seleccionado ningún ítem para editar.');
             return;
        }

        $datosValidados = $this->validate($this->rules('editar'));

        // Mapear nombres de propiedades validadas a nombres de columna BD
        $datosParaActualizar = [
            // No actualizamos materia_id
            'nombre' => $datosValidados['nombreEditar'],
            'corte_escuela_id' => $datosValidados['corte_escuela_idEditar'],
            'tipo_item_id' => $datosValidados['tipo_item_idEditar'],
            'contenido' => $datosValidados['contenidoEditar'],
            'visible_predeterminado' => $datosValidados['visible_predeterminadoEditar'],
            'entregable_predeterminado' => $datosValidados['entregable_predeterminadoEditar'],
            'porcentaje_sugerido' => $datosValidados['porcentaje_sugeridoEditar'],
            'orden' => $datosValidados['ordenEditar'],
        ];

        try {
            $item = ItemPlantilla::find($this->itemIdEditar);
            if ($item) {
                $item->update($datosParaActualizar);
                session()->flash('success', 'Plantilla de ítem actualizada correctamente.');
                $this->cerrarOffcanvasEditar(); // Llama al método que despacha el evento JS
                $this->cargarDatos(); // Recargar la lista de ítems
            } else {
                 session()->flash('error', 'No se encontró el ítem para actualizar.');
            }

        } catch (\Exception $e) {
            Log::error('Error al actualizar ItemPlantilla ID ' . $this->itemIdEditar . ': ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al actualizar el ítem: ' . $e->getMessage());
             // No cerrar el offcanvas en caso de error
        }
    }

    // --- Método ELIMINAR Ítem ---
    /**
     * Elimina una plantilla de ítem.
     * La confirmación se maneja vía JS con SweetAlert.
     *
     * @param int $id El ID del ItemPlantilla a eliminar.
     */
    public function eliminarItem($id)
    {
         $item = ItemPlantilla::where('id', $id)->where('materia_id', $this->materia->id)->first(); // Seguridad
         if ($item) {
             // **Validación Adicional (Opcional pero recomendada):**
             // Verificar si esta plantilla ya ha sido usada para crear instancias (ItemCorteMateriaPeriodo)
             // if ($item->itemInstancias()->exists()) { // Asumiendo que tienes la relación itemInstancias() en ItemPlantilla
             //     session()->flash('error', "No se puede eliminar '{$item->nombre}' porque ya tiene instancias creadas en periodos.");
             //     return;
             // }

             try {
                 $nombreItem = $item->nombre;
                 $item->delete();
                 session()->flash('success', "Plantilla de ítem '{$nombreItem}' eliminada correctamente.");
                 $this->cargarDatos(); // Recargar lista
             } catch (\Exception $e) {
                 Log::error('Error al eliminar ItemPlantilla ID ' . $id . ': ' . $e->getMessage());
                 session()->flash('error', 'Ocurrió un error al eliminar el ítem.');
             }
         } else {
             session()->flash('error', 'Ítem no encontrado o no pertenece a esta materia.');
         }
    }


    // --- INICIO: Métodos para MODAL DUPLICAR MODELO ---
    

     // --- Métodos para Modal Duplicar Modelo ---
     public function abrirModalDuplicar()
     {
         $this->materiaIdFuenteParaDuplicar = null;
         $this->resetErrorBag(); // Limpiar errores previos del modal
         session()->forget('errorModalDuplicar'); // Limpiar mensaje de error específico del modal
 
         if ($this->materia->escuela_id) {
             $this->materiasParaDuplicar = Materia::where('escuela_id', $this->materia->escuela_id)
                 ->where('id', '!=', $this->materia->id) // Excluir la materia actual
                 ->orderBy('nombre')
                 ->get();
         } else {
             $this->materiasParaDuplicar = collect();
             session()->flash('errorModalDuplicar', 'La materia actual no está asociada a una escuela, no se pueden listar otras materias.');
         }
         
         $this->dispatch('abrirOffcanvas', nombreModal: 'modalDuplicarModelo');
     }
 
     public function cerrarModalDuplicar()
     {
         $this->dispatch('cerrarOffcanvas', nombreModal: 'modalDuplicarModelo');
         $this->materiaIdFuenteParaDuplicar = null;
         $this->resetErrorBag();
         session()->forget('errorModalDuplicar');
     }
 
     public function duplicarModeloDeMateria()
     {
         $this->validate([
             'materiaIdFuenteParaDuplicar' => 'required|exists:materias,id',
         ], [
             'materiaIdFuenteParaDuplicar.required' => 'Debes seleccionar una materia de origen.',
             'materiaIdFuenteParaDuplicar.exists' => 'La materia de origen seleccionada no es válida.',
         ]);
 
         $materiaFuente = Materia::find($this->materiaIdFuenteParaDuplicar);
 
         if (!$materiaFuente) {
             session()->flash('errorModalDuplicar', 'Materia de origen no encontrada.');
             // No cerrar el modal para que el usuario vea el error
             return;
         }
 
         $itemsFuente = ItemPlantilla::where('materia_id', $materiaFuente->id)->get();
 
         if ($itemsFuente->isEmpty()) {
             session()->flash('errorModalDuplicar', "La materia de origen '{$materiaFuente->nombre}' no tiene plantillas de ítems para duplicar.");
             // No cerrar el modal
             return;
         }
 
         DB::beginTransaction();
         try {
             $itemsDuplicadosConExito = 0;
             foreach ($itemsFuente as $itemFuente) {
                 // Opcional: Verificar si un ítem con el mismo nombre y corte ya existe en la materia destino
                 // para evitar duplicados exactos si no se desea. Por ahora, se duplica directamente.
 
                 ItemPlantilla::create([
                     'materia_id' => $this->materia->id, // ID de la materia actual (destino)
                     'corte_escuela_id' => $itemFuente->corte_escuela_id,
                     'tipo_item_id' => $itemFuente->tipo_item_id,
                     'nombre' => $itemFuente->nombre, // Considerar añadir prefijo/sufijo si se necesita unicidad
                     'contenido' => $itemFuente->contenido,
                     'visible_predeterminado' => $itemFuente->visible_predeterminado,
                     'entregable_predeterminado' => $itemFuente->entregable_predeterminado,
                     'porcentaje_sugerido' => $itemFuente->porcentaje_sugerido,
                     'orden' => $itemFuente->orden, // Considerar ajustar el orden si hay colisiones
                 ]);
                 $itemsDuplicadosConExito++;
             }
             DB::commit();
             session()->flash('status', "Se han duplicado {$itemsDuplicadosConExito} plantilla(s) de ítems desde '{$materiaFuente->nombre}' a '{$this->materia->nombre}' exitosamente.");
             $this->cerrarModalDuplicar();
             $this->cargarItemPlantillas();
 
         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error al duplicar modelo de ítems desde materia ID {$materiaFuente->id} a materia ID {$this->materia->id}: " . $e->getMessage());
             session()->flash('errorModalDuplicar', 'Ocurrió un error al intentar duplicar el modelo. Por favor, inténtalo de nuevo.');
             // No cerrar el modal
         }
     }
    // --- FIN: Métodos para MODAL DUPLICAR MODELO ---

    /**
     * Renderiza la vista del componente.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    { $this->cargarDatos();
        return view('livewire.escuelas.gestion-item-plantillas');
    }
}
