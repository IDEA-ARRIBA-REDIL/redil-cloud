<?php

namespace App\Livewire\FormulariosParaUsuarios;

use App\Models\CampoFormularioUsuario;
use App\Models\Configuracion;
use App\Models\SeccionFormularioUsuario;
use Livewire\Component;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;


use Illuminate\Support\Facades\Storage;

class GestionarSeccionesYCampos extends Component
{
  public $formulario;
  public $secciones = [];
  public $variable = [0];

  public $seccionesActivas = [];

  public $respuesta = 'dd';
  public $campos = [];

  /* Campos para el funcionamiento de editar y crear seccion  */
  public $nombre;
  public $título;
  public $modoEdicion = false;
  public $seccionEditando;

  /* Campos para el funcionamiento de editar y crear campos  */
  public $class;
  public $campoRequerido;
  public $campo;
  public $informacionDeApoyo;
  //public $título;
  public $modoEdicionCampo = false;
  public $campoEditando;
  public $seccionCampo;

  /* Dependencia de campos */
  public $tieneDependencia = false;
  public $dependeDeCampoId = null;
  public $tipoCondicion = 'no_vacio';
  public $valorCondicion = null;
  public $accionDependencia = 'deshabilitar';


  // otros
  public $configuracion ;

  protected $rules = [
    'nombre' => 'required',
    'título' => 'required',
  ];

  protected $rulesCampos = [
    'class' => 'required'
  ];

  public function mount()
  {
     $this->configuracion = Configuracion::find(1);
  }

  public function updatedTieneDependencia($value)
  {
    if (!$value) {
      $this->dependeDeCampoId = null;
      $this->tipoCondicion = 'no_vacio';
      $this->valorCondicion = null;
      $this->accionDependencia = 'deshabilitar';
    }
  }

  public function updatedDependeDeCampoId($value)
  {
    $this->valorCondicion = null;
  }

  public function getCamposDisponiblesParaDependenciaProperty()
  {
    if (!$this->formulario) {
      return collect();
    }

    $seccionesIds = $this->formulario->secciones()->pluck('id')->toArray();

    $camposIdsUsados = DB::table('campo_seccion_formulario_usuario')
      ->whereIn('seccion_id', $seccionesIds)
      ->pluck('campo_id')
      ->toArray();

    $query = CampoFormularioUsuario::whereIn('id', $camposIdsUsados);

    if ($this->modoEdicionCampo && $this->campoEditando) {
      $query->where('id', '!=', $this->campoEditando->id);
    } elseif ($this->campo) {
      $query->where('id', '!=', $this->campo);
    }

    return $query->orderBy('nombre', 'asc')->get();
  }

  public function getOpcionesValorCondicionPadreProperty()
  {
    if (!$this->dependeDeCampoId) {
      return [];
    }

    $campoPadre = CampoFormularioUsuario::find($this->dependeDeCampoId);
    if (!$campoPadre) {
      return [];
    }

    // Si es un Switch / Checkbox
    if ($campoPadre->name_id === 'tienesUnaPeticion' || $campoPadre->name_id === 'preguntaVivesEn' || str_contains(strtolower($campoPadre->nombre_bd ?? ''), 'switch')) {
      return [
        '1' => 'Marcado / Encendido (SÍ)',
        '0' => 'Desmarcado / Apagado (NO)'
      ];
    }

    // Si es un Campo Extra con opciones select (tipo 3 o 4)
    if (!empty($campoPadre->opciones_select)) {
      $opciones = json_decode($campoPadre->opciones_select, true);
      if (is_array($opciones)) {
        $resultado = [];
        foreach ($opciones as $op) {
          $val = $op['value'] ?? $op['nombre'] ?? '';
          $nombre = $op['nombre'] ?? $val;
          $resultado[$val] = ucwords($nombre);
        }
        return $resultado;
      }
    }

    // Si es un campo base tipo selector
    $nombreBd = $campoPadre->nombre_bd ?? $campoPadre->name_id;
    switch ($nombreBd) {
      case 'tipo_identificacion_id':
        return \App\Models\TipoIdentificacion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'estado_civil_id':
        return \App\Models\EstadoCivil::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'genero_id':
        return \App\Models\Genero::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'tipo_sangre_id':
        return \App\Models\TipoSangre::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'profesion_id':
        return \App\Models\Profesion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'estatus_id':
        return \App\Models\Estatus::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'tipo_vivienda_id':
        return \App\Models\TipoVivienda::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'tipo_vinculacion_id':
        return \App\Models\TipoVinculacion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'sede_id':
        return \App\Models\Sede::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'ocupacion_id':
        return \App\Models\Ocupacion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'sector_economico_id':
        return \App\Models\SectorEconomico::orderBy('nombre')->pluck('nombre', 'id')->toArray();
      case 'rango_edad_id':
        return \App\Models\RangoEdad::orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }

    return [];
  }

  // esta funcion prepara las variables para abrir el modal de crearCampo
  public function crearCampo($seccionId)
  {
    $this->seccionesActivas = [$seccionId];
    $this->modoEdicionCampo = false;
    $this->reset(['class', 'campoRequerido', 'informacionDeApoyo', 'tieneDependencia', 'dependeDeCampoId', 'tipoCondicion', 'valorCondicion', 'accionDependencia']);
    $this->tipoCondicion = 'no_vacio';
    $this->accionDependencia = 'deshabilitar';
    $this->seccionCampo =  SeccionFormularioUsuario::find($seccionId); // Almacena la sección actual de campo
    $this->class="col-12 col-sm-6 col-md-4 col-lg-3";
    $this->dispatch('abrirModal', nombreModal: 'modalNuevoCampo');

    $this->campo = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeCampos::class);
  }

   // esta funcion prepara las variables para abrir el modal de editarCampo
  public function editarCampo($seccionId, $campoId)
  {
    $this->seccionCampo =  SeccionFormularioUsuario::find($seccionId); // Almacena la sección actual de campo
    $this->campoEditando =  $this->seccionCampo->campos()->where('campos_formulario_usuario.id', $campoId)->first();
    $this->seccionesActivas = [$seccionId];
    $this->modoEdicionCampo = true;

    // formateo el formulario
    $this->reset(['class', 'campoRequerido', 'informacionDeApoyo', 'tieneDependencia', 'dependeDeCampoId', 'tipoCondicion', 'valorCondicion', 'accionDependencia']);
    $this->campo = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeCampos::class);
    //fin formateo formulario

    $this->class= $this->campoEditando->pivot->class;
    $this->informacionDeApoyo = $this->campoEditando->pivot->informacion_de_apoyo;
    $this->campoRequerido= $this->campoEditando->pivot->requerido;
    $this->dependeDeCampoId = $this->campoEditando->pivot->depende_de_campo_id;
    $this->tieneDependencia = !empty($this->dependeDeCampoId);
    $this->tipoCondicion = $this->campoEditando->pivot->tipo_condicion ?? 'no_vacio';
    $this->valorCondicion = $this->campoEditando->pivot->valor_condicion;
    $this->accionDependencia = $this->campoEditando->pivot->accion_dependencia ?? 'deshabilitar';
    $this->dispatch('abrirModal', nombreModal: 'modalNuevoCampo');
  }

   // esta funcion guarda o edita los datos en la BD
  public function guardarCampo()
  {
    $validatedData = Validator::make($this->all(), $this->rulesCampos)->validate();

    if ($this->modoEdicionCampo) {
        // Actualizar el campo existente
        $this->seccionCampo->campos()->updateExistingPivot(
          $this->campoEditando->id, // Usa el ID del campo a editar
          [
            'requerido' => $this->campoRequerido,
            'class' => $this->class,
            'informacion_de_apoyo' => $this->informacionDeApoyo,
            'depende_de_campo_id' => $this->tieneDependencia && $this->dependeDeCampoId ? $this->dependeDeCampoId : null,
            'tipo_condicion' => $this->tieneDependencia && $this->dependeDeCampoId ? ($this->tipoCondicion ?? 'no_vacio') : null,
            'valor_condicion' => $this->tieneDependencia && $this->dependeDeCampoId && $this->tipoCondicion === 'igual_a' ? $this->valorCondicion : null,
            'accion_dependencia' => $this->tieneDependencia && $this->dependeDeCampoId ? ($this->accionDependencia ?? 'deshabilitar') : 'deshabilitar',
          ]
        );

        $this->dispatch('cerrarModal', nombreModal: 'modalNuevoCampo');
        $this->reset('campoEditando', 'modoEdicionCampo');

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          msnTexto: 'El campo fue editado con éxito.'
        );

    } else {
        // Crear un nuevo campo
        if(!$this->campo)
        {
          // valido y mando el error a componente anidado
          $this->dispatch('mostrarMensajeError',
          mostrarError: true,
          msnError: 'El campo es requerido.'
          )->to(SelectorDeCampos::class);

          $this->respuesta = $this->campo;
        }else{
          $this->respuesta = $this->campo;

          $this->dispatch('mostrarMensajeError',
          mostrarError: false,
          msnError: ''
          )->to(SelectorDeCampos::class);

          $this->seccionCampo->campos()->attach( $this->campo, [
            'class' => $this->class,
            'requerido' => $this->campoRequerido ? true : false,
            'orden' => $this->seccionCampo->campos()->count() + 1,
            'informacion_de_apoyo' => $this->informacionDeApoyo,
            'depende_de_campo_id' => $this->tieneDependencia && $this->dependeDeCampoId ? $this->dependeDeCampoId : null,
            'tipo_condicion' => $this->tieneDependencia && $this->dependeDeCampoId ? ($this->tipoCondicion ?? 'no_vacio') : null,
            'valor_condicion' => $this->tieneDependencia && $this->dependeDeCampoId && $this->tipoCondicion === 'igual_a' ? $this->valorCondicion : null,
            'accion_dependencia' => $this->tieneDependencia && $this->dependeDeCampoId ? ($this->accionDependencia ?? 'deshabilitar') : 'deshabilitar',
          ]);

          $this->dispatch('cerrarModal', nombreModal: 'modalNuevoCampo');

          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'El campo fue creado con éxito.'
          );
        }


    }

  }

  // esta funcion prepara las variables para abrir el modal de crearSeccion
  public function crearSeccion()
  {
    // Resetea los campos del formulario
    $this->seccionEditando = null;
    $this->reset(['nombre', 'título']);

    $this->modoEdicion = false;

    // Emitir evento para abrir el offcanvas (opcional)
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaSeccion');
  }

  // esta funcion prepara las variables para abrir el modal de editarSeccion
  public function editarSeccion($seccionId)
  {
      // Resetea los campos del formulario
      $this->seccionEditando = null;
      $this->reset(['nombre', 'título']);

      $this->modoEdicion = true;
      $this->seccionEditando = SeccionFormularioUsuario::find($seccionId);
      $this->nombre = $this->seccionEditando->nombre;
      $this->título = $this->seccionEditando->titulo;

      // Emitir evento para abrir el offcanvas (opcional)
      $this->dispatch('abrirModal', nombreModal: 'modalNuevaSeccion');
  }

  public function guardarSeccion()
  {
    $validatedData = Validator::make($this->all(), $this->rules)->validate();

    if ($this->modoEdicion) {
      // Actualizar la sección existente
      $this->seccionEditando->nombre = $this->nombre;
      $this->seccionEditando->titulo = $this->título;
      $this->seccionEditando->save();

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaSeccion');
      $this->modoEdicion = false;
      $this->reset('seccionEditando');

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue editada con éxito.'
      );
    }else{
      $seccion = new SeccionFormularioUsuario;
      $seccion->nombre = $validatedData['nombre'];
      $seccion->titulo = $validatedData['título'];
      $seccion->formulario_usuario_id = $this->formulario->id;

      $ultimaSeccionActual = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->orderBy('orden', 'desc')->first();
      $seccion->orden = $ultimaSeccionActual ? $ultimaSeccionActual->orden + 1 : 1;
      $seccion->save();

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaSeccion');
      $this->modoEdicion = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue creada con éxito.'
      );
    }
  }

  public function actualizarOrdenCampos($campoId, $seccionOrigenId, $seccionDestinoId, $ordenOrigen, $ordenDestino)
  {
      $this->seccionesActivas = [$seccionDestinoId,$seccionOrigenId];

      $seccionDestinoId = (int) $seccionDestinoId;

      // Obtener el registro pivot actual
      $campoPivot = DB::table('campo_seccion_formulario_usuario')
      ->where('campo_id', $campoId)
      ->where('seccion_id', $seccionOrigenId)
      ->update(['seccion_id' => $seccionDestinoId]);

      // Actualizar orden en la sección de destino
      $seccionDestino = SeccionFormularioUsuario::find($seccionDestinoId);

      $ordenDestino = array_filter($ordenDestino);
      foreach ($ordenDestino as $index => $id) {
        $this->respuesta = $seccionDestino->campos()->get();
        $seccionDestino->campos()->updateExistingPivot($id, ['orden' => $index + 1]);
      }

      //Si la sección de origen es diferente a la de destino
      if ($seccionOrigenId!== $seccionDestinoId) {
        $seccionOrigen = SeccionFormularioUsuario::find($seccionOrigenId);
        $ordenOrigen = array_filter($ordenOrigen);
        foreach ($ordenOrigen as $index => $id) {
          $seccionOrigen->campos()->updateExistingPivot($id, ['orden' => $index + 1]);
        }
      }
  }

  public function subirSeccion($id)
  {
    $this->seccionesActivas = [];
    $seccionActual = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->find($id);
    $seccionAnterior = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->where('orden', '<', $seccionActual->orden)->orderBy('orden', 'desc')->first();

    if ($seccionAnterior) {
        $ordenAnterior = $seccionAnterior->orden;
        $seccionAnterior->orden = $seccionActual->orden;
        $seccionActual->orden = $ordenAnterior;

        $seccionActual->save();
        $seccionAnterior->save();
    }
  }

  public function bajarSeccion($id)
  {
    $this->seccionesActivas = [];
    $seccionActual = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->find($id);
    $seccionSiguiente = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->where('orden', '>', $seccionActual->orden)->orderBy('orden')->first();

    if ($seccionSiguiente) {
        $ordenSiguiente = $seccionSiguiente->orden;
        $seccionSiguiente->orden = $seccionActual->orden;
        $seccionActual->orden = $ordenSiguiente;

        $seccionActual->save();
        $seccionSiguiente->save();
    }
  }

  public function eliminarCampo($campoId, $seccionId)
  {

    $seccion = SeccionFormularioUsuario::find($seccionId);
    $seccion->campos()->detach($campoId);

    // Si el campo eliminado era el que se estaba editando, lo limpiamos del estado.
    if ($this->campoEditando && $this->campoEditando->id == $campoId) {
        $this->reset('campoEditando', 'modoEdicionCampo');
    }
  }

  public function eliminarSeccion($seccionId)
  {
    $seccion = SeccionFormularioUsuario::find($seccionId);

    if ($seccion) {
        $formularioId = $seccion->formulario_usuario_id;
        $ordenEliminado = $seccion->orden;

        //Desvincula todos los campos
        $seccion->campos()->detach();

        // Si la sección eliminada era la que se estaba editando, la limpiamos del estado.
        if ($this->seccionEditando && $this->seccionEditando->id == $seccionId) {
            $this->reset('seccionEditando');
        }

        $seccion->delete();

        // Reordenar secciones restantes
        $seccionesRestantes = SeccionFormularioUsuario::where('formulario_usuario_id', $formularioId)
            ->where('orden', '>', $ordenEliminado)
            ->orderBy('orden')
            ->get();

        foreach ($seccionesRestantes as $s) {
            $s->orden = $s->orden - 1;
            $s->save();
        }
    }
  }

  #[On('obtenerCampoSeleccionado')]
  public function obtenerCampoSeleccionado($id)
  {
    $this->campo = $id;
  }

  public function render()
  {
    $this->secciones = $this->formulario->secciones()->orderBy('orden','asc')->get();
      return view('livewire.formularios-para-usuarios.gestionar-secciones-y-campos');
  }
}
