<?php

namespace App\Livewire\CamposFormularioUsuario;

use App\Models\CampoFormularioUsuario;
use App\Models\Role;
use Livewire\Component;


use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class GestionarCampos extends Component
{

  public $busqueda = '';
  public $conEliminados = false;
  public $soloCamposExtra = false;
  public $entre = 'no';
  public $tipoCampos = ['Input', 'Text', 'Select', 'Select multiple'];
  public $listaRoles = null;

  public $modoEdicionCampo = false;
  /*campos para formulario de nuevo campo*/
  public $campoEditar;
  public $nombre, $nameId, $placeholder, $tipoDeCampo, $opcionesSelect, $visibleResumen, $roles;

  public function mount()
  {
    $this->listaRoles = Role::select('id','name','icono')->orderBy('name','asc')->get();
  }

  public function ocultarMostrar($campoId)
  {
    $campo = CampoFormularioUsuario::withTrashed()->find($campoId);

    if ($campo->trashed()) {
      $campo->restore();

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Buen trabajo!',
        msnTexto: 'El campo se restauró con éxito.'
      );
    } else {
      $campo->delete();

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Buen trabajo!',
        msnTexto: 'El campo se ocultó con éxito.'
      );
    }

  }

  public function eliminarCampo($campoId)
  {
    $campo = CampoFormularioUsuario::withTrashed()->find($campoId);

    // eliminar toda la información de esta campo con el usuario
    $campo->usuarios()->delete();

    // eliminar tooda la relación que tiene con los formularios
    $campo->secciones()->delete();

    $campo->forceDelete();
  }


  // esta funcion prepara las variables para abrir el modal de crearCampo
  public function crearCampo()
  {
    $this->resetErrorBag();
    $this->modoEdicionCampo = false;
    $this->reset(['nombre', 'nameId', 'tipoDeCampo', 'opcionesSelect', 'visibleResumen']);
    $this->opcionesSelect='[{ "id": "", "nombre":"", "visible":"1" ,"value":"" }]';

    $this->dispatch('abrirModal', nombreModal: 'modalcrearEditarCampo');
  }

   // esta funcion prepara las variables para abrir el modal de editarCampo
  public function editarCampo($campoId)
  {
    $this->resetErrorBag();
    $this->modoEdicionCampo = true;
    $this->reset(['nombre', 'nameId', 'tipoDeCampo', 'opcionesSelect', 'visibleResumen']);

    $this->campoEditar = CampoFormularioUsuario::find($campoId);

    $this->nombre = $this->campoEditar->nombre;
    $this->placeholder = $this->campoEditar->placeholder;
    $this->visibleResumen = $this->campoEditar->visible_resumen;
    $this->nameId = $this->campoEditar->name_id;

    if($this->campoEditar->es_campo_extra)
    {
      $this->placeholder = $this->campoEditar->placeholder;
      $this->tipoDeCampo = $this->campoEditar->tipo_de_campo;
      $this->opcionesSelect = $this->campoEditar->opciones_select;
    }

    $this->dispatch('abrirModal', nombreModal: 'modalcrearEditarCampo');
  }

  public function guardarCampo()
  {
    $rules = [
      'nombre' => 'required',
      'placeholder' => 'nullable',
    ];

    if($this->modoEdicionCampo==false || ($this->modoEdicionCampo==true && $this->campoEditar->es_campo_extra))
    {
      $rules = array_merge($rules, ['nameId' => 'required', 'tipoDeCampo' => 'required', ]);
      if($this->tipoDeCampo > 2)
      {
        $rules = array_merge($rules, ['nameId' => 'required', 'tipoDeCampo' => 'required', 'opcionesSelect' => 'required']);
      }
    }

    $validatedData = Validator::make($this->all(), $rules)->validate();

    $this->entre = $validatedData;
    if ($this->modoEdicionCampo)
    {
      $this->entre = 'si,editar';
      $this->campoEditar->nombre = $this->nombre;
      $this->campoEditar->placeholder = $this->placeholder;
      $this->campoEditar->visible_resumen = $this->visibleResumen;

      if($this->campoEditar->es_campo_extra)
      {
        $this->campoEditar->name_id = $this->nameId;
        $this->campoEditar->tipo_de_campo = $this->tipoDeCampo;
        $this->campoEditar->opciones_select = $this->opcionesSelect;
      }
      $this->campoEditar->save();
    }else{
      $this->entre = 'si,nuevo';
      $campo = new CampoFormularioUsuario;
      $campo->nombre = $validatedData['nombre'];
      $campo->name_id = $validatedData['nameId'];
      $campo->placeholder = $validatedData['placeholder'];
      $campo->es_campo_extra = true;
      $campo->tipo_de_campo = $validatedData['tipoDeCampo'];
      $campo->visible_resumen = $this->visibleResumen ? true : false;

      if($validatedData['tipoDeCampo']>2)
      $campo->opciones_select = $validatedData['opcionesSelect'];

      $campo->save();
    }

    $this->dispatch('cerrarModal', nombreModal: 'modalcrearEditarCampo');
    $this->modoEdicionCampo = false;
    $this->dispatch(
      'msn',
      msnIcono: 'success',
      msnTitulo: '¡Muy bien!',
      msnTexto: 'El campo fue creado con éxito.'
    );
  }

  public function render()
  {
    $campos = CampoFormularioUsuario::whereRaw('1=1');

    if ($this->conEliminados) {
      $campos->withTrashed();
    }

    if ($this->soloCamposExtra)
    {
      $campos->where('es_campo_extra', true);
    }

    $campos = $campos->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$this->busqueda%'")
    ->orderBy('nombre', 'ASC')
    ->get();

    return view('livewire.campos-formulario-usuario.gestionar-campos', [
      'campos' => $campos,
    ]);
  }
}
