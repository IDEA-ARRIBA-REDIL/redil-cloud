<?php

namespace App\Livewire\Grupos;

use Livewire\Component;

use App\Helpers\Helpers;
use App\Models\Grupo;
use App\Models\TipoAsignacion;
use stdClass;

class GruposParaBusqueda extends Component
{
  // antiguo postObtieneGruposParaBusquedaAjax

  public $id,
    $class,
    $label,
    $placeholder, // placeholder del campo input
    $obligatorio = false, // si es TRUE coloca el asterisco para indicar que el input es obligatorio
    $conDadosDeBaja, // Para saber si carga con los dados de baja.
    $multiple = false, // Para saber si solo se carga un grupo o varios grupos.
    $grupoSeleccionadoId, // Cuando multiple es false va a precargar este id
    $gruposSeleccionadosIds = [], // Cuando multiple es true va precargar estos grupos
    $cantGruposCargados = 10,
    $estiloSeleccion = null, // Si es null es el estilo basico que es (foto, nombre y tipo de usuario)
    $redirect = null, // si existe re dirije a una ruta especifica
    $redirectClose = null, // si existe re dirije a una ruta especifica cuando quita el grupo (X)

    // --- Parametros de vinculación y desvinculación ---//
    $tieneInformeDeVinculacion = false, // Si es TRUE, una vez seleccionado el grupo debe de abrir el modal para escribir el informe de vinculación al grupo
    $tieneInformeDeDesvinculacion = false, // Si es TRUE, una vez se de en el btn de eliminar el grupo debe de abrir el modal para escribir el informe de desvinculación al grupo

    $validarPrivilegiosTipoGrupo = false, // Si es TRUE verifica si el ROL activo tiene el privilegio de asignar/desvincular grupo según el tipo de grupo
    $usuario = null; // Si tiene usuario lo envio como parametro

  public $busqueda = '',
    $grupoSeleccionado = null,
    $gruposSeleccionados = [],
    $verListaBusqueda = false,
    $verInputBusqueda = true,
    $tiposGruposPrivilegioAsignar,
    $tiposGruposPrivilegioDesvincular,
    $idsGruposDondeAsisteActualmente = [],
    $bitacoras = [],
    $tipoAsignacionDefault = null,
    $tiposAsignaciones = null,
    $tiposDesvinculacion = null;

  // Inputs motivoModalAsignacion
  public $motivoModalAsignacion, $observacionModalAsigancion;
  // Inputs modalInformeDesvinculacion
  public $motivoModalDesvinculacion, $observacionModalDesvinculacion, $desvinculacionDeServiciosModalDesvinculacion;
  public $idGrupoModal;

  public $rolActivo;
  protected $listeners = ['cargarMas'];

  public $bandera = "";

  public function mount()
  {
    $this->rolActivo = auth()
      ->user()
      ->roles()
      ->wherePivot('activo', true)
      ->first();

    if ($this->multiple == FALSE) {
      if ($this->grupoSeleccionadoId) {
        $this->seleccionarGrupo($this->grupoSeleccionadoId);
      }
    } else {
      if ($this->gruposSeleccionadosIds) {
        $this->seleccionarGrupos();
      }
    }

    if ($this->validarPrivilegiosTipoGrupo) {
      $this->tiposGruposPrivilegioAsignar = $this->rolActivo->privilegiosTiposGrupo()
        ->wherePivot("asignar_asistente", "=", FALSE)
        ->select('tipo_grupos.id')
        ->pluck('tipo_grupos.id')
        ->toArray();

      $this->tiposGruposPrivilegioDesvincular = $this->rolActivo->privilegiosTiposGrupo()
        ->wherePivot("desvincular_asistente", "=", FALSE)
        ->select('tipo_grupos.id')
        ->pluck('tipo_grupos.id')
        ->toArray();
    }

    if ($this->tieneInformeDeVinculacion || $this->tieneInformeDeDesvinculacion) {
      $this->idsGruposDondeAsisteActualmente = $this->usuario->gruposDondeAsiste->pluck('id')->toArray();

      $this->tipoAsignacionDefault = TipoAsignacion::where('default', true)->first();
      $this->tiposAsignaciones = TipoAsignacion::where('para_asignar_asistentes', true)->get();
      $this->tiposDesvinculacion = TipoAsignacion::where('para_desvincular_asistentes', true)->get();
    }
  }

  public function desplegarListaBusqueda()
  {
    $this->verListaBusqueda = true;
  }

  public function ocultarListaBusqueda()
  {
    $this->verListaBusqueda = false;
  }

  public function resetCantidadGruposCargados()
  {
    $this->cantGruposCargados = 10;
  }

  public function cargarMas()
  {
    $this->cantGruposCargados += 1;
  }

  public function quitarSeleccion($grupoId = null)
  {
    $quitar = true;

    if ($grupoId) {
      $grupo = Grupo::find($grupoId);

      if ($this->validarPrivilegiosTipoGrupo)
        $quitar = in_array($grupo->tipoGrupo->id, $this->tiposGruposPrivilegioDesvincular) ? false : true;
    }

    if ($quitar) {
      if ($this->multiple) {
        $this->gruposSeleccionadosIds = array_values(array_diff($this->gruposSeleccionadosIds, array($grupoId)));
        $this->gruposSeleccionadosIds = array_unique($this->gruposSeleccionadosIds);
        $this->gruposSeleccionados = Grupo::whereIn('id', $this->gruposSeleccionadosIds)->get();

        if ($this->tieneInformeDeDesvinculacion) {
          if (in_array($grupoId, $this->idsGruposDondeAsisteActualmente)) {
            // añado json bitacora default y abro modal para que el usuario complete la opcion
            $item = new stdClass();
            $item->bitacora = 'desvinculacion';
            $item->grupoId = $grupoId;
            $item->observacion = '';
            $item->motivoId = $this->tipoAsignacionDefault->id;
            $item->desvincularServicios = 'no';
            $this->bitacoras[] = $item;

            if ($this->rolActivo->hasPermissionTo('grupos.mostar_modal_informe_desvinculacion_de_asistentes')) {
              $this->idGrupoModal = $grupoId;
              $this->resetErrorBag(); // Establece los mensajes de error en la validacion
              $this->dispatch('abrirModal', nombreModal: 'modalInformeDesvinculacion');
            }
          } else {
            // pregunto si existe un registro en el json de bitacoras
            $indice = 0;
            foreach ($this->bitacoras as $bitacora) {
              if ($bitacora->grupoId == $grupoId) {
                $indiceEliminiar = $indice;
              }
              $indice++;
            }
            //unset($this->bitacoras[$indiceEliminiar]);
            array_splice($this->bitacoras, $indiceEliminiar, 1);
          }
        }
      } else {
        $this->grupoSeleccionado = null;
        $this->grupoSeleccionadoId = '';
        // emitir disparador para cuando se desee usar desde otro componente anidado
        $this->dispatch('grupo-id-anidado',  grupoId: null);
      }

      if ($this->redirectClose && !$this->grupoSeleccionadoId) {
        $this->redirectRoute($this->redirectClose);
      }
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: '¡Ups!',
        msnTexto: 'No tienes los privilegios suficientes para desvincular a la persona de este grupo. Por favor, consulte a su administrador.'
      );
    }

    if ($this->estiloSeleccion == 'pequeno') {
      $this->verInputBusqueda = true;
      $this->verListaBusqueda = true;
    }
  }

  // Funcion para cuando sea multiple == FALSE
  public function seleccionarGrupo($grupoId)
  {
    $agregar = true;
    $grupo = Grupo::find($grupoId);

    if ($this->validarPrivilegiosTipoGrupo) {
      $agregar = in_array($grupo->tipoGrupo->id, $this->tiposGruposPrivilegioAsignar) ? false : true;
    }

    if ($agregar) {
      $this->cantGruposCargados = 3;
      $this->grupoSeleccionado = $grupo;

      if ($this->estiloSeleccion == 'pequeno') {
        $this->verInputBusqueda = false;
      }
      $this->verListaBusqueda = false;

      // emitir disparador para cuando se desee usar desde otro componente anidado
      $this->dispatch('grupo-id-anidado',  grupoId: $grupo->id);
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: '¡Ups!',
        msnTexto: 'No tienes los privilegios suficientes para agregar a la persona de este grupo. Por favor, consulte a su administrador.'
      );
    }
  }

  // Funcion para cuando sea multiple == TRUE
  public function seleccionarGrupos($grupoId = null)
  {
    $agregar = true;
    if ($grupoId) {
      $this->bandera = $grupoId;
      $grupo = Grupo::find($grupoId);

      if ($this->validarPrivilegiosTipoGrupo)
        $agregar = in_array($grupo->tipoGrupo->id, $this->tiposGruposPrivilegioAsignar) ? false : true;

      if ($agregar) {
        if (in_array($grupoId, $this->gruposSeleccionadosIds) == false) {

          array_push($this->gruposSeleccionadosIds, $grupoId);
          $this->gruposSeleccionadosIds = array_unique($this->gruposSeleccionadosIds);
          $grupos = Grupo::whereIn('id', $this->gruposSeleccionadosIds)->get();
          $this->cantGruposCargados = 3;
          $this->gruposSeleccionados = $grupos;

          if ($this->tieneInformeDeVinculacion) {
            //bitacora con modal
            if (in_array($grupoId, $this->idsGruposDondeAsisteActualmente)) {
              // pregunto si existe un registro en el json de bitacoras
              $indice = 0;
              foreach ($this->bitacoras as $bitacora) {
                if ($bitacora->grupoId == $grupoId) {
                  $indiceEliminiar = $indice;
                }
                $indice++;
              }
              //unset($this->bitacoras[$indiceEliminiar]);
              array_splice($this->bitacoras, $indiceEliminiar, 1);
            } else {
              // añado json bitacora default y abro modal para que el usuario complete la opcion
              $item = new stdClass();
              $item->bitacora = 'asignacion';
              $item->grupoId = $grupoId;
              $item->observacion = '';
              $item->motivoId = $this->tipoAsignacionDefault->id;
              $this->bitacoras[] = $item;

              if ($this->rolActivo->hasPermissionTo('grupos.mostar_modal_informe_asignacion_de_asistentes')) {
                $this->idGrupoModal = $grupoId;
                $this->resetErrorBag(); // Establece los mensajes de error en la validacion
                $this->dispatch('abrirModal', nombreModal: 'modalInformeAsignacion');
              }
            }
          }
        }
      } else {
        $this->dispatch(
          'msn',
          msnIcono: 'warning',
          msnTitulo: '¡Ups!',
          msnTexto: 'No tienes los privilegios suficientes para agregar a la persona de este grupo. Por favor, consulte a su administrador.'
        );
      }
    } else {
      $grupos = Grupo::whereIn('id', $this->gruposSeleccionadosIds ? $this->gruposSeleccionadosIds : [])->get();
      $this->cantGruposCargados = 3;
      $this->gruposSeleccionados = $grupos;
    }

    $this->verListaBusqueda = false;
  }

  // submit de modal informe asignacion
  public function informeAsignacion()
  {
    $this->validate(['motivoModalAsignacion' => 'required']);
    foreach ($this->bitacoras as $bitacora) {
      if ($bitacora->grupoId == $this->idGrupoModal) {
        $bitacora->observacion = $this->observacionModalAsigancion;
        $bitacora->motivoId = $this->motivoModalAsignacion;
        $this->bitacoras = $this->bitacoras;
      }
    }
    $this->dispatch('cerrarModal', nombreModal: 'modalInformeAsignacion');
  }

  // submit de modal informe desvinculacion
  public function informeDesvinculacion()
  {
    $this->validate(['motivoModalDesvinculacion' => 'required']);
    foreach ($this->bitacoras as $bitacora) {
      if ($bitacora->grupoId == $this->idGrupoModal) {
        $bitacora->observacion = $this->observacionModalDesvinculacion;
        $bitacora->motivoId = $this->motivoModalDesvinculacion;
        $bitacora->desvincularServicios = $this->desvinculacionDeServiciosModalDesvinculacion ? 'si' : 'no';
        $this->bitacoras = $this->bitacoras;
      }
    }
    $this->dispatch('cerrarModal', nombreModal: 'modalInformeDesvinculacion');
  }

  public function render()
  {
    if ($this->conDadosDeBaja == 'si') {
      $grupos = Grupo::whereRaw('(1=1)');
    } else {
      $grupos = Grupo::where('grupos.dado_baja', 0);
    }

    if ($this->rolActivo->hasPermissionTo('grupos.ajax_obtiene_grupos_solo_ministerio')) {
      if ($this->conDadosDeBaja == 'si') {
        $grupos = auth()
          ->user()
          ->gruposMinisterio();
      } else {
        $grupos = auth()
          ->user()
          ->gruposMinisterio()
          ->where('grupos.dado_baja', '=', 0);
      }
    }

    if ($this->busqueda && strlen($this->busqueda) >= 3) {
      $buscar = htmlspecialchars($this->busqueda);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      $c = 0;
      $sql_buscar = '';
      foreach ($buscar_array as $palabra) {
        if ($c != 0) {
          $sql_buscar .= ' AND ';
        }
        $sql_buscar .= "(translate (grupos.nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'
         OR translate (encargado.identificacion,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'
         OR translate (encargado.primer_nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'
         OR translate (encargado.segundo_nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'
         OR translate (encargado.primer_apellido,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'
         OR translate (encargado.segundo_apellido,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'";
        if (ctype_digit($palabra)) {
          $sql_buscar .= " OR grupos.id=$palabra";
        }
        $sql_buscar .= ')';
        $c++;
      }

      $grupos = $grupos
        ->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
        ->leftJoin('users AS encargado', 'encargados_grupo.user_id', '=', 'encargado.id')
        ->whereRaw($sql_buscar)
        ->select('grupos.*');
    }

    $grupos = $grupos->orderBy('grupos.id', 'DESC')->paginate($this->cantGruposCargados);
    return view('livewire.grupos.grupos-para-busqueda', ['grupos' => $grupos]);
  }

  /*
  public function grupoSeleccionado($grupoId)
  {
    $grupo = Grupo::find($grupoId);
    $tipoUsuarioActivo = Auth::user()
      ->tiposUsuarios()
      ->wherePivot('activo', '=', true)
      ->first();
    $respuesta =
      '<div class="grupo_seleccionado" data-tipo-grupo-id="' .
      $grupo->tipoGrupo->id .
      '" style="padding: 5px;" id="item-' .
      $class .
      '-' .
      $id .
      '" class="col-lg-' .
      $col_lg .
      ' col-md-' .
      $col_lg .
      ' col-sm-' .
      $col_sm .
      ' col-xs-' .
      $col_sm .
      '">';
    $respuesta .= '<div class="item-seleccionado">';
    $respuesta .= '<div id="ico-' . $class . '" class="col-xs-4 col-sm-4 col-md-3 col-lg-3 bg-orange" >';
    $respuesta .=
      '<center><i class="fa fa fa-share-alt fa-3x" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i></center>';
    $respuesta .= '</div>';
    $respuesta .= '<div id="info-' . $class . '" class="info-item col-xs-7 col-sm-7 col-md-8 col-lg-8 ">';
    $respuesta .= '<h4 class="titulo"><b>' . $class . ' </b></h4>';
    $respuesta .= '<h3 class="capitalize">' . $grupo->nombre . '</h3>';
    $respuesta .= '<p>' . $grupo->codigo . '</p>';
    if ($grupo->encargados()->count() > 0) {
      foreach ($grupo->encargados()->get() as $encargado) {
        $respuesta .=
          '<label class="label arrowed-right " style="background-color: ' .
          $encargado->tipoAsistente->color .
          ';" data-toggle="tooltip" data-placement="top" title="' .
          $encargado->tipoAsistente->nombre .
          '"><i class="fa ' .
          $encargado->tipoAsistente->icono .
          '" style="margin-right:15 px;"> </i></label>';

        $respuesta .=
          ' <span class="capitalize">' .
          $encargado->primer_nombre .
          ' ' .
          $encargado->segundo_nombre .
          ' ' .
          $encargado->primer_apellido .
          ' ' .
          $encargado->segundo_apellido .
          '</span><br>';
      }
    } else {
      $respuesta .= 'Esta grupo no tiene ningun encargado. ';
    }
    $respuesta .= '</p></div>';
    $respuesta .=
      '<div class="cerrar no-padding col-xs-1 col-sm-1 col-md-1 col-lg-1" style="border-color:#fff" alert alert-success>';
    if ($tipoUsuarioActivo->opcion_desvincular_asistentes_grupos == true) {
      $respuesta .=
        '<button id="cerrar-' .
        $class .
        '-' .
        $grupo->id .
        '" data-id="' .
        $id .
        '" name="cerrar-' .
        $class .
        '-' .
        $grupo->id .
        '" type="button" class="close  cerrar-' .
        $class .
        '-seleccionado" style="font-size:27px;outline:none" aria-hidden="true">×</button>
			';
    }

    $respuesta .= '</div> </div></div>';
    return $respuesta;
  }*/
}
