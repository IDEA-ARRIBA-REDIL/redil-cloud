<?php

namespace App\Livewire\Actividades;

use App\Models\Actividad;
use App\Models\ElementoFormularioActividad;
use App\Models\TipoElementoFormularioActividad;
use Livewire\Attributes\On;
// Componentes de Livewire

use Livewire\Component;

class FormularioActividad extends Component
{
    public $elementos = [];

    public $tipos;

    public $actividad;

    public $actividadesTotales = [];

    public $actividadIduplicar;

    public $ordenElementos = [];

    // Inicializador de las variables del nuevo elemento

    public $titulo;

    public $tipo_elemento_id;

    public $required;

    public $visible;

    public $visible_asistencia;

    public $descripcion;

    // / Esta variable es para cuando se abre el modal identificar el elemento
    // / Se usa en el metodo  abrir off canvas
    public $elementoId;

    // / Variables para poder hacer la edicion del elemento
    public $elementoSeleccionado;

    public $tipoElemento;

    public $opciones;

    public $showOffcanvas = false;

    // Esto es para la funcionalidad del offcanvas al editar un elemento
    public $newTag = '';

    public $nuevaOpcion = '';

    public $opcionesElementosActualizadas;

    // Iniciar variable del form de editar el offcanvas
    public $elementoTitulo;

    public $elementoRequired;

    public $elementoVisible;

    public $elementoVisibleAsistencia;

    public $elementoDescripcion;

    public $elementoTipo;

    public $pesoMaximo;

    public $altoImagen;

    public $anchoImagen;

    public $pesoMaximoArchivo;

    public function mount()
    {
        $this->tipos = TipoElementoFormularioActividad::all();
        $this->actividadesTotales = Actividad::all();

        $this->cargarElementos();
    }

    public function cargarElementos()
    {
        $this->elementos = ElementoFormularioActividad::with(['tipoElemento'])->where('actividad_id', $this->actividad->id)->orderBy('orden', 'asc')->get();
    }

    // / metodo para validar el modal de nuevo son dos sencillas
    public function rules()
    {
        $rules = [
            'titulo' => 'required|string|max:100',
            'tipo_elemento_id' => 'required',
        ];

        return $rules;
    }

    // / metodo para los mensajes de error personalizados
    public function messages()
    {
        $messages = [
            'titulo.required' => 'Por favor, ingrese el nombre del elemento o pregunta.',
            'tipo_elemento_id.required' => 'Seleccione un tipo de elemento',
        ];

        return $messages;
    }

    // / Aqui esta la funcion para guardar el nuevo elemento
    public function guardar()
    {
        $validatedData = $this->validate();
        $tipo_elemento = TipoElementoFormularioActividad::find($this->tipo_elemento_id);

        $maxOrden = ElementoFormularioActividad::where('actividad_id', $this->actividad->id)->max('orden') ?? 0;

        $elementoModel = new ElementoFormularioActividad;
        $elementoModel->titulo = $this->titulo;
        $elementoModel->tipo_elemento_id = $this->tipo_elemento_id;
        $elementoModel->required = (bool) $this->required;
        $elementoModel->visible = $this->visible !== null ? (bool) $this->visible : true;
        $elementoModel->visible_asistencia = (bool) $this->visible_asistencia;
        $elementoModel->descripcion = $this->descripcion;
        $elementoModel->actividad_id = $this->actividad->id;
        $elementoModel->orden = $maxOrden + 1;

        $elementoModel->save();

        $this->reset(['titulo', 'tipo_elemento_id', 'required', 'visible', 'visible_asistencia', 'descripcion']);
        $this->resetValidation();

        $this->cargarElementos();

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'El elemento fue creado con éxito.'
        );

        $this->dispatch('cerrarModal', nombreModal: 'modalNuevoElemento');
    }

    // / ACTUALIZA EL ORDEN DE LOS ELEMENTOS DEL FORMULARIO

    #[On('updateOrders')]
    public function updateOrders($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            $elementoActual = ElementoFormularioActividad::find($id);
            $elementoActual->orden = $index;
            $elementoActual->save();
        }

        $this->cargarElementos();
    }

    // // aqui con este metodo se abre el off canvas y se inicializa el contenido
    #[On('abrir-offcanvas')]
    public function abrirOffcanvas($elementoId)
    {
        $this->elementoId = $elementoId;
        $elemento = ElementoFormularioActividad::with(['tipoElemento'])
            ->find($this->elementoId);

        $this->elementoTitulo = $elemento->titulo;
        $this->elementoRequired = (bool) $elemento->required;
        $this->elementoVisible = (bool) $elemento->visible;
        $this->elementoVisibleAsistencia = (bool) $elemento->visible_asistencia;
        $this->elementoDescripcion = $elemento->descripcion;
        $this->elementoTipo = $elemento->tipoElemento->id;

        $this->pesoMaximoArchivo = $elemento->peso_maximo;
        $this->pesoMaximo = $elemento->peso_maximo;
        $this->anchoImagen = $elemento->ancho;
        $this->altoImagen = $elemento->largo;

        $this->elementoSeleccionado = $elemento;
        $this->tipoElemento = $elemento->tipoElemento;
        $this->opcionesElementosActualizadas = $this->elementoSeleccionado->opciones;
        $this->showOffcanvas = true;

        // Dispara un evento JavaScript para abrir el offcanvas
        $this->dispatch('abrirOffcanvas');
    }

    // // aqui con este metodo se guarda el formulario del off canvas y reinicia el mount
    public function ActualizarElemento()
    {
        $this->elementoSeleccionado->titulo = $this->elementoTitulo;
        if ($this->elementoRequired == 1) {
            $this->elementoSeleccionado->required = true;
        } else {
            $this->elementoSeleccionado->required = false;
        }

        if ($this->elementoVisible == 1) {
            $this->elementoSeleccionado->visible = true;
        } else {
            $this->elementoSeleccionado->visible = false;
        }

        if ($this->elementoVisibleAsistencia == 1) {
            $this->elementoSeleccionado->visible_asistencia = true;
        } else {
            $this->elementoSeleccionado->visible_asistencia = false;
        }

        if ($this->elementoSeleccionado->tipoElemento->clase == 'imagen') {
            $this->elementoSeleccionado->peso_maximo = $this->pesoMaximo;
            $this->elementoSeleccionado->largo = $this->altoImagen;
            $this->elementoSeleccionado->ancho = $this->anchoImagen;
        }

        if ($this->elementoSeleccionado->tipoElemento->clase == 'archivo') {
            $this->elementoSeleccionado->peso_maximo = $this->pesoMaximoArchivo;
        }

        $this->elementoSeleccionado->descripcion = $this->elementoDescripcion;
        $this->elementoSeleccionado->tipo_elemento_id = $this->elementoTipo;
        $this->elementoSeleccionado->save();

        $this->cargarElementos();

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'El elemento fue actualizado con éxito.'
        );
    }

    // // este metodo es el que al poner un nombre en el input de la seccion wire:keydown.space.prevent="addOpcion" y luego dar espacio ejectua esta funcion
    public function addOpcion()
    {
        if (! empty(trim($this->nuevaOpcion))) {
            $this->elementoSeleccionado->opciones()->create([
                'valor_texto' => trim($this->nuevaOpcion),
            ]);
            $this->elementoSeleccionado->load('opciones');
            $this->opcionesElementosActualizadas = $this->elementoSeleccionado->opciones;
        }

        $this->nuevaOpcion = '';
    }

    // // este metodo es el que elimina una opcion del arreglo, y de la base de datos
    public function removeOpcion($opcion)
    {
        $this->elementoSeleccionado->opciones()->where('valor_texto', $opcion)->delete();
        $this->elementoSeleccionado->load('opciones');
        $this->opcionesElementosActualizadas = $this->elementoSeleccionado->opciones;
    }

    public function confirmarEliminarElemento($elementoId)
    {
        $this->dispatch('confirmarEliminarElemento', elementoId: $elementoId);
    }

    // eliminar elemento

    public function eliminarElemento($elementoId)
    {

        $actividadCategorias = $this->actividad->categorias()->where('aforo_ocupado', '>', 0)->first();
        if (isset($actividadCategorias->id)) {
            $this->dispatch(
                'msn',
                msnIcono: 'alert',
                msnTitulo: '¡Opps!',
                msnTexto: 'lo sentimos ya existe un aforo para esta actividad no puedes eliminar este elemento, puedes configurarlo como no visible.'
            );
        } else {

            $elementoEliminar = ElementoFormularioActividad::find($elementoId);
            $elementoEliminar->opciones()->delete();
            $elementoEliminar->delete();

            $this->dispatch(
                'msn',
                msnIcono: 'success',
                msnTitulo: '¡Muy bien!',
                msnTexto: 'el elemento fue eliminado con éxito.'
            );
        }
    }

    // duplicar elemento

    public function duplicarElemento($actividadOrigenId)
    {
        if (empty($this->actividadIduplicar)) {
            $this->dispatch(
                'msn',
                msnIcono: 'warning',
                msnTitulo: '¡Atención!',
                msnTexto: 'Por favor, selecciona una actividad de la cual duplicar los elementos.'
            );

            return;
        }

        $elementosOriginales = ElementoFormularioActividad::where('actividad_id', $this->actividadIduplicar)
            ->with('opciones')
            ->orderBy('orden', 'asc')
            ->get();

        if ($elementosOriginales->isEmpty()) {
            $this->dispatch(
                'msn',
                msnIcono: 'info',
                msnTitulo: 'Sin elementos',
                msnTexto: 'La actividad seleccionada no tiene elementos para duplicar.'
            );

            return;
        }

        $maxOrden = ElementoFormularioActividad::where('actividad_id', $actividadOrigenId)->max('orden') ?? 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($actividadOrigenId, $elementosOriginales, &$maxOrden) {
            foreach ($elementosOriginales as $elementoOriginal) {
                $maxOrden++;
                // Crear nuevo elemento con actividad_id actual
                $nuevoElemento = $elementoOriginal->replicate();
                $nuevoElemento->actividad_id = $actividadOrigenId;
                $nuevoElemento->orden = $maxOrden;
                $nuevoElemento->save();

                // Duplicar opciones si existen
                foreach ($elementoOriginal->opciones as $opcion) {
                    $nuevaOpcion = $opcion->replicate();
                    $nuevaOpcion->elemento_formulario_actividad_id = $nuevoElemento->id;
                    $nuevaOpcion->save();
                }
            }
        });

        $this->cargarElementos();
        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: '¡Los elementos fueron duplicados con éxito!'
        );
        $this->dispatch('cerrarModal', nombreModal: 'modalDuplicaeElemento');
    }

    public function render()
    {

        return view('livewire.actividades.formulario-actividad');
    }
}
