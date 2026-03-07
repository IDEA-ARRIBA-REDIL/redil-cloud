<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use App\Models\ActividadCategoria;
use App\Models\ActividadCategoriaMoneda;
use App\Models\Actividad;

class ActualizarActividadCategoria extends Component
{
    public $categoria=null;
    public $actividadMonedas=null;
    public $actividad=null;
    public $categoriaId=null;
    public $name=null;
    public $actualizarValorMoneda=null;

    public $respuesta = 'Nada por el momento';

    /* formulario de actualizar */
    public $nombreCategoriaActualizar;

    public $categoriaIdEditar;
    public $nombreEditar;
    public $aforoEditar;
    public $esGratuitaEditar = false;
    public $valoresMonedasEditar = [];


    protected $rules = [
      'nombreCategoriaActualizar' => 'required'
    ];


    public function mount()
    {

    }

    #[On('abrir-modal-actualizar-categoria')]
    public function abrirModalActualizarCategoria($categoriaId)
    {
        // Buscar la categoría a editar
        $categoria = ActividadCategoria::findOrFail($categoriaId);

        // Rellenar campos del formulario
        $this->categoriaIdEditar = $categoria->id;
        $this->nombreEditar = $categoria->nombre;
        $this->aforoEditar = $categoria->aforo;
        $this->esGratuitaEditar = $categoria->es_gratuita;


        // Restablecer y popular valores de monedas si no es gratuita
        $this->valoresMonedasEditar = [];
        if (!$this->esGratuitaEditar) {
            foreach ($this->monedasActividad as $moneda) {
                // Obtener el valor para esta moneda de la tabla pivote
                $valorMoneda = $categoria->monedas()->where('moneda_id', $moneda->id)->first();
                $this->valoresMonedasEditar[$moneda->id] = $valorMoneda ? $valorMoneda->pivot->valor : null;
            }
        }
    }

     // Método para actualizar la categoría
     public function actualizarCategoria()
     {
         // Reglas de validación
         $validacion = [
             'nombreEditar' => 'required',
             'aforoEditar' => 'required|numeric|min:1'
         ];

         $mensajesValidacion = [
             'nombreEditar.required' => 'Este campo es obligatorio',
             'aforoEditar.required' => 'Este campo es obligatorio',
             'aforoEditar.numeric' => 'Debe ser numérico',
             'aforoEditar.min' => 'Este campo debe ser mínimo 1'
         ];

         // Validación dinámica para campos de monedas si no es gratuita
         if (!$this->esGratuitaEditar) {
             foreach ($this->monedasActividad as $moneda) {
                 $validacion['valoresMonedasEditar.'.$moneda->id] = ["required","numeric","min:1"];
                 $mensajesValidacion['valoresMonedasEditar.'.$moneda->id.'.required'] = 'Este campo es obligatorio';
                 $mensajesValidacion['valoresMonedasEditar.'.$moneda->id.'.numeric'] = 'Este campo debe ser numérico';
                 $mensajesValidacion['valoresMonedasEditar.'.$moneda->id.'.min'] = 'Este campo debe ser mínimo 1';
             }
         }

         // Validar entrada
         $this->validate($validacion, $mensajesValidacion);

         // Buscar la categoría a actualizar
         $categoria = ActividadCategoria::findOrFail($this->categoriaIdEditar);

         // Actualizar detalles de la categoría
         $categoria->nombre = $this->nombreEditar;
         $categoria->aforo = $this->aforoEditar;
         $categoria->es_gratuita = $this->esGratuitaEditar;
         $categoria->save();

         // Manejar valores de monedas si no es gratuita
         if (!$this->esGratuitaEditar) {
             $monedasParaSync = [];
             foreach ($this->monedasActividad as $moneda) {
                 $valor = $this->valoresMonedasEditar[$moneda->id] ?? null;
                 if ($valor !== null) {
                     $monedasParaSync[$moneda->id] = ['valor' => $valor];
                 }
             }
             $categoria->monedas()->sync($monedasParaSync);
         } else {
             // Si ahora es gratuita, desasociar todas las monedas
             $categoria->monedas()->detach();
         }

         // Enviar mensaje de éxito
         $this->dispatch(
             'msn',
             msnIcono: 'success',
             msnTitulo: '¡Buen trabajo!',
             msnTexto: 'La categoría se actualizó con éxito.'
         );

         // Restablecer campos del formulario
         $this->reset(['categoriaIdEditar', 'nombreEditar', 'aforoEditar', 'esGratuitaEditar', 'valoresMonedasEditar']);
     }

    public function render()
    {
        return view('livewire.actividades.actualizar-actividad-categoria');
    }
}
