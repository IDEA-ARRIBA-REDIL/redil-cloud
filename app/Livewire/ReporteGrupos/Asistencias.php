<?php

namespace App\Livewire\ReporteGrupos;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\GrupoExcluido;
use App\Models\Ingreso;
use App\Models\Moneda;
use App\Models\Ofrenda;
use App\Models\TipoInasistencia;
use App\Models\TipoOfrenda;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;

use Livewire\Attributes\On;

class Asistencias extends Component
{
  public $reporte;
  public $grupo;
  public $tipoGrupo;
  public $configuracion;
  public $sumatorias = [];
  public $ofrendasGenerica = [];
  public $personas = [];
  public $encargados = null;
  public $personasFiltradas = null;
  public $moneda;
  public $tipoInasistencias;
  public $tiposInasistenciaConObservacionObligatoria = [];
  public $tiposOfrendasEspecificas = [];
  public $idsTiposOfrendasEspecificas = [];
  public $dador = null;
  public $tieneClasificacion = 'si';
  public $verDivClacificacion = true;
  public $search = '';
  public $hola = 'eeee';
  public $idsEncargados = [];

  // ofrendas especificas
  public $inputsTemporalOfrendasEspecificasUsuario = [];
  public $ofrendasTemporalEspecificasUsuario = [];

  // elementos del formulario
  public $inputsOfrendasGenerica = [];
  public $inputsSumatoriasAdiccionales = [];
  public $togglesEncargadosAsistencia = [];
  public $togglesAsistencia = []; // esta es la de los miembros de grupo
  public $selectInasistencias = []; // esta es la de los miembros de grupo
  public $observacionesInasistencia = [];
  public array $ofrendasPorPersona = []; // tiene toda la data de las ofrendas especificas por persona

  private function iniciarEncargados()
  {
    // informacion encargado
    if (!isset($this->reporte->informacion_encargado_grupo))
    {
      $informacionEncargado = [];
      $encargados = $this->grupo->encargados;
      foreach ($encargados as $encargado) {
          $informacionEncargado[] = [
              'id' => $encargado->id,
              'nombre' => $encargado->nombre(3),
              'asistio' => true,
          ];

          $this->togglesEncargadosAsistencia[$encargado->id] = true;
          $this->idsEncargados = [$encargado->id];
      }

      $this->reporte->informacion_encargado_grupo = $informacionEncargado;
      $this->reporte->save();
    }else{
      foreach ($this->reporte->informacion_encargado_grupo as $encargado)
      {
        $this->idsEncargados = $encargado['id'];
        $this->togglesEncargadosAsistencia[$encargado['id']] = $encargado['asistio'] ?? false;
      }
    }
  }

  private function inicarAsistentesAlGrupo()
  {
    // Obtén los asistentes del reporte (incluyendo los dados de baja) con su estado de asistencia
    $asistentesReporte = $this->reporte->usuarios()
      ->withTrashed()
      ->select('users.id','foto','email','primer_nombre','segundo_nombre','primer_apellido','segundo_nombre','identificacion','tipo_identificacion_id','tipo_usuario_id', 'asistencia_grupos.asistio', 'asistencia_grupos.tipo_inasistencia_id', 'asistencia_grupos.observaciones')
      ->get();

    // Obtén los IDs de los asistentes del reporte en un array
    $arrayAsistieron = $asistentesReporte->pluck('id')->toArray();

    // Obtén los integrantes actuales del grupo que no están en la lista de asistentes del reporte
    $asistentesActuales = $this->grupo->asistentes()
        ->whereNotIn('users.id', $arrayAsistieron)
        ->select('users.id','foto','email','primer_nombre','segundo_nombre','primer_apellido','segundo_nombre','identificacion','tipo_identificacion_id','tipo_usuario_id')
        ->get();

    $todosLosAsistentes = collect();

    // Procesa los asistentes del reporte añadiendo los campos 'asistio' y 'asistioCambio'
    foreach ($asistentesReporte as $asistente) {
        $todosLosAsistentes->push((object) [
            'id' => $asistente->id,
            'foto' => $asistente->foto,
            'primer_nombre' => $asistente->primer_nombre,
            'segundo_nombre' => $asistente->segundo_nombre,
            'primer_apellido' => $asistente->primer_apellido,
            'segundo_apellido' => $asistente->segundo_apellido,
            'identificacion' => $asistente->identificacion,
            'tipo_identificacion_id' => $asistente->tipo_identificacion_id,
            'iniciales_nombre' => $asistente->inicialesNombre(),
            'nombre' => $asistente->nombre(3),
            'email' => $asistente->email,
            'tipo_usuario_nombre' => $asistente->tipoUsuario->nombre,
            'tipo_usuario_icono' => $asistente->tipoUsuario->icono,
            'asistio' => $asistente->asistio,
            'asistioCambio' => $asistente->asistio,
        ]);

        $this->togglesAsistencia[$asistente->id] = $asistente->asistio;
        $this->selectInasistencias[$asistente->id] = $asistente->tipo_inasistencia_id;
        $this->observacionesInasistencia[$asistente->id] = $asistente->observaciones;
    }

    // Procesa los asistentes actuales añadiendo el campo 'asistioCambio' con valor false
    foreach ($asistentesActuales as $asistente) {
        $todosLosAsistentes->push((object) [
            'id' => $asistente->id,
            'foto' => $asistente->foto,
            'primer_nombre' => $asistente->primer_nombre,
            'segundo_nombre' => $asistente->segundo_nombre,
            'primer_apellido' => $asistente->primer_apellido,
            'segundo_apellido' => $asistente->segundo_apellido,
            'identificacion' => $asistente->identificacion,
            'tipo_identificacion_id' => $asistente->tipo_identificacion_id,
            'iniciales_nombre' => $asistente->inicialesNombre(),
            'nombre' => $asistente->nombre(3),
            'email' => $asistente->email,
            'tipo_usuario_nombre' => $asistente->tipoUsuario->nombre,
            'tipo_usuario_icono' => $asistente->tipoUsuario->icono,
            'asistio' => false,
            'asistioCambio' => false,
        ]);

        $this->togglesAsistencia[$asistente->id] = false;
        $this->selectInasistencias[$asistente->id] = null;
        $this->observacionesInasistencia[$asistente->id] = '';
    }

    $this->personas = $todosLosAsistentes->sortBy('nombre');

  }

  private function iniciarOfrendasGenericas()
  {
    // ofrendasGenericas
    $tiposOfrendasIds = $this->tipoGrupo->tiposOfrendas->where('generica', true)->pluck('id')->toArray(); // tipos de ofrenda genericas del tipo de grupo

    $ofrendasGenericasReporteIds = $this->reporte
    ->ofrendas()
    ->whereRelation('tipoOfrenda', 'generica', true) // <-- AQUÍ LA MAGIA ✨
    ->distinct() // Agrega distinct() por si varias ofrendas comparten el mismo tipo
    ->pluck('tipo_ofrenda_id')
    ->toArray();

    $idsUnicos = array_values(array_unique(array_merge($tiposOfrendasIds, $ofrendasGenericasReporteIds)));
    $tiposOfrendasGenericas = TipoOfrenda::whereIn('id', $idsUnicos)->get();

    $ofrendasGenericas = collect();
    foreach ($tiposOfrendasGenericas as $tipoOfrendaGenerica) {

    $ofrendaTemporal = $this->reporte->ofrendas()->where('tipo_ofrenda_id', '=', $tipoOfrendaGenerica->id )->first();

    $ofrendasGenericas->push((object) [
      'tipo_ofrenda_id' => $tipoOfrendaGenerica->id,
      'nombre' => $tipoOfrendaGenerica->nombre,
      'valor' => $ofrendaTemporal ? $ofrendaTemporal->valor : '',
      'ofrenda_obligatoria' => $tipoOfrendaGenerica->ofrenda_obligatoria
    ]);

    $this->inputsOfrendasGenerica[$tipoOfrendaGenerica->id]['valor'] =  $ofrendaTemporal ? $ofrendaTemporal->valor : '';

    }
    $this->ofrendasGenerica = $ofrendasGenericas;
  }

  private function iniciarOfrendasEspecificas()
  {

    $idsDadores = collect([]);

    if ($this->tipoGrupo->ingresos_individuales_discipulos) {
        $idsDadores = $idsDadores->merge($this->personas->pluck('id'));
    }

    // 3. Segundo bloque de condición
    if ($this->tipoGrupo->ingresos_individuales_lideres) {
        $idsDadores = $idsDadores->merge($this->idsEncargados);
    }

    // Precargar las ofrendas existentes para cada persona
    foreach ($idsDadores as $dadorId) {
      $this->ofrendasPorPersona[$dadorId] = $this->reporte
      ->ofrendas()
      ->where('user_id', $dadorId)
      ->whereRelation('tipoOfrenda', 'generica', false)
      ->select('ofrendas.id', 'ofrendas.tipo_ofrenda_id', 'ofrendas.valor')
      ->get()
      ->keyBy('tipo_ofrenda_id')
      ->toArray();
    }
  }

  private function iniciarClasificacionesSumatorias()
  {

    $existenClasificaciones = $this->reporte->clasificaciones()->select('clasificaciones_asistentes.id')->count();

    if(!$existenClasificaciones)
    {
      $clasificacionesAsistentes = $this->tipoGrupo->clasificacionAsistentes()->orderBy('orden','asc')->get();

      if ($clasificacionesAsistentes && $clasificacionesAsistentes->isNotEmpty())
      {
        foreach ($clasificacionesAsistentes as $clasificacionAsistente) {
          $dataParaAttach[$clasificacionAsistente->id] = ['cantidad' => 0];
        }

        $this->reporte->clasificaciones()->attach($dataParaAttach);
      }

    }

    // Clasificaciones
    $clasificacionesSumatorias = collect();
    $clasificaciones = $this->reporte->clasificaciones()->where('tiene_sumatoria_adicional', '=', TRUE)->orderBy('orden','asc')->get();
      foreach ($clasificaciones as $clasificacion) {
      $clasificacionesSumatorias->push((object) [
          'id' => $clasificacion->id,
          'nombre' => $clasificacion->nombre,
          'cantidad' => $clasificacion->pivot->cantidad,
      ]);

      $this->inputsSumatoriasAdiccionales[$clasificacion->id]['valor'] =  $clasificacion->pivot->cantidad;
    }

    $this->sumatorias = $clasificacionesSumatorias;
  }

  public function mount()
  {
    $this->configuracion = Configuracion::first();
    $this->grupo = $this->reporte->grupo;
    $this->tipoGrupo = $this->grupo->tipoGrupo;

    if($this->tipoGrupo->registrar_inasistencia){
      $this->tipoInasistencias = TipoInasistencia::orderBy('nombre','asc')->get();

      if ($this->tipoInasistencias) {
        foreach ($this->tipoInasistencias as $tipo) {
          if ($tipo->observacion_obligatoria) {
            $this->tiposInasistenciaConObservacionObligatoria[$tipo->id] = true;
          }
        }
      }
    }

    $this->moneda = Moneda::where('default', true)->first();

    $this->iniciarEncargados();
    $this->inicarAsistentesAlGrupo();
    $this->iniciarClasificacionesSumatorias();
    $this->iniciarOfrendasGenericas();

    if($this->tipoGrupo->ingresos_individuales_discipulos || $this->tipoGrupo->ingresingresos_individuales_lideres)
    $this->iniciarOfrendasEspecificas();

    $this->tiposOfrendasEspecificas = $this->tipoGrupo->tiposOfrendas()->where('generica', false)->get(); // tipos de ofrenda especificas del tipo de grupo
    $this->idsTiposOfrendasEspecificas =  $this->tiposOfrendasEspecificas->pluck('id')->toArray();



  }

 /* public function actualizarAsistencia(int $personaId, bool $asistio)
  {
    // Aquí tienes el ID de la persona ($personaId) y el nuevo estado del checkbox ($asistio)
    $this->hola = "El ID de la persona es: " . $personaId . " y asistió: " . ($asistio ? 'Sí' : 'No');

    $asistenteParaActualizar = $this->personas->firstWhere('id', $personaId);

    if ($asistenteParaActualizar) {
      $asistenteParaActualizar->asistio = ($asistio ? true : false);
      $this->loadData();
    }
  }*/

  public function updatedTieneClasificacion($value)
  {
    $this->verDivClacificacion = ($value === 'si');

    if( $value === 'no' )
    {
      $this->sumatorias = $this->sumatorias->map(function ($item) {
        $this->inputsSumatoriasAdiccionales[$item->id]['valor'] = 0;
        $item->cantidad = 0;
        return $item;
    });
    }
  }

  public function updatedSelectInasistencias($value, $key)
  {
      // Si el nuevo tipo de inasistencia seleccionado NO requiere observación,
      // y podría existir una observación previa, la limpiamos.
      if (!isset($this->tiposInasistenciaConObservacionObligatoria[$value])) {
          if (isset($this->observacionesInasistencia[$key])) {
              $this->observacionesInasistencia[$key] = '';
          }
      }
      // La visibilidad del input de observación se manejará en Blade basado en
      // $this->selectInasistencias[$key] y $this->tiposInasistenciaConObservacionObligatoria.
  }

  private function loadData()
  {
    // filtrar
    if (strlen($this->search) > 2)
    {
      $this->personasFiltradas =  collect($this->personas);
      $buscar = htmlspecialchars($this->search);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $this->personasFiltradas = $this->personasFiltradas->filter(function ($persona) use ($palabra) {
          $respuesta = false !== stristr(Helpers::sanearStringConEspacios($persona->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($persona->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($persona->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($persona->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($persona->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($persona->email), $palabra);

          return $respuesta;
        });
      }
    }else{
      $this->personasFiltradas = null;
    }
  }

  public function updatedSearch()
  {
    $this->loadData();
  }

  public function restarClasificacion(int $clasificacionId)
  {
    $this->actualizarCantidad($clasificacionId, -1);
  }

  public function sumarClasificacion(int $clasificacionId)
  {
    $this->actualizarCantidad($clasificacionId, 1);
  }

  private function actualizarCantidad(int $clasificacionId, int $incremento)
  {
      $this->sumatorias = $this->sumatorias->map(function ($item) use ($clasificacionId, $incremento) {
          if ($item->id === $clasificacionId) {
              $nuevaCantidad = $item->cantidad + $incremento;
              $item->cantidad = max(0, $nuevaCantidad); // Asegura que no sea menor que 0

              $this->inputsSumatoriasAdiccionales[$clasificacionId]['valor'] =  max(0, $nuevaCantidad);
          }
          return $item;
      });
  }

  public function abrirModalOfrendaEspecifica($userId)
  {
    $this->dador = User::select('id','primer_nombre','segundo_nombre','primer_apellido', 'segundo_apellido')->find($userId);

    // Esta parte no necesita cambios, sigue funcionando para obtener los IDs de los tipos de ofrenda
    $tipoOfrendasEspecificasUsuarioReporteIds = array_keys($this->ofrendasPorPersona[$userId] ?? []);
    $idsUnicos = array_values(array_unique(array_merge($this->idsTiposOfrendasEspecificas, $tipoOfrendasEspecificasUsuarioReporteIds)));
    $tiposOfrenda = TipoOfrenda::whereIn('id', $idsUnicos)->get();

    $ofrendasUsuario = collect();
    $this->inputsTemporalOfrendasEspecificasUsuario = []; // Limpiar antes de llenar

    // Obtener las ofrendas (con su id y valor) desde nuestra colección temporal principal
    $ofrendasActuales = $this->ofrendasPorPersona[$userId] ?? [];

    foreach ($tiposOfrenda as $tipoOfrenda)
    {
      $valorActual = $ofrendasActuales[$tipoOfrenda->id]['valor'] ?? 0.00;
      $ofrendaIdActual = $ofrendasActuales[$tipoOfrenda->id]['id'] ?? null;

      $ofrendasUsuario->push((object) [
        'ofrenda_id' => $ofrendaIdActual,
        'tipo_ofrenda_id' => $tipoOfrenda->id,
        'nombre' => $tipoOfrenda->nombre,
        'valor' => $valorActual
      ]);

      $this->inputsTemporalOfrendasEspecificasUsuario[$tipoOfrenda->id]['valor'] =  $valorActual;

      // También guardamos el id en nuestro array temporal, puede ser útil.
      $this->inputsTemporalOfrendasEspecificasUsuario[$tipoOfrenda->id]['id'] = $ofrendaIdActual;
    }
    $this->ofrendasTemporalEspecificasUsuario = $ofrendasUsuario;

    $this->dispatch('abrirModal', nombreModal: 'modalOfrendaEspecifica');
  }

  public function guardarOfrendaTemporal()
  {
    // La validación sigue siendo correcta para el campo 'valor'

    $rules = [];
    $messages = [];

    foreach ($this->ofrendasTemporalEspecificasUsuario as $ofrenda) {
        $campo = "inputsTemporalOfrendasEspecificasUsuario.{$ofrenda->tipo_ofrenda_id}.valor";
        $rules[$campo] = 'numeric|min:0';
        $messages[$campo . '.numeric'] = 'El valor de ' . $ofrenda->nombre . ' debe ser un número.';
        $messages[$campo . '.min'] = 'El valor de ' . $ofrenda->nombre . ' debe ser mayor o igual a :min.';
    }

    if($rules)
    $this->validate($rules, $messages);

    if ($this->dador) {
        $userId = $this->dador->id;
        $this->ofrendasPorPersona[$userId] = collect($this->inputsTemporalOfrendasEspecificasUsuario)

            // Luego, mapeamos para asegurar que la estructura de datos sea la correcta.
            ->mapWithKeys(function ($item, $key) {
                // $key es el tipo_ofrenda_id
                // $item es ahora ['id' => ..., 'valor' => ...]

                // Devolvemos un array con la llave principal (tipo_ofrenda_id)
                // y como valor, el array completo con la estructura que necesitamos.
                return [
                    $key => [
                        'id' => $item['id'], // Preservamos el ID original de la ofrenda
                        'tipo_ofrenda_id' => $key,
                        'valor' => $item['valor'] > 0 ? $item['valor'] : 0,
                    ]
                ];
            })
            ->toArray();
    }


    $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'Las ofrendas de ' . $this->dador->nombre(3) . ' añadidas.'
    );
    $this->dispatch('cerrarModal', nombreModal: 'modalOfrendaEspecifica');
  }

  public function submitFormulario()
  {
    $this->search = '';
    $this->loadData();
    $rules = [];
    $messages = [];

    if($this->reporte->aprobado === null)
    {

      foreach ($this->ofrendasGenerica as $ofrenda) {
          $campo = "inputsOfrendasGenerica.{$ofrenda->tipo_ofrenda_id}.valor";
          $rules[$campo] = $ofrenda->ofrenda_obligatoria ? 'required|numeric|min:0' : 'nullable|numeric|min:0';
          $messages[$campo . '.required'] = 'El valor de ' . $ofrenda->nombre . ' es obligatorio.';
          $messages[$campo . '.numeric'] = 'El valor de ' . $ofrenda->nombre . ' debe ser un número.';
          $messages[$campo . '.min'] = 'El valor de ' . $ofrenda->nombre . ' debe ser mayor o igual a :min.';
      }
    }


    // --- Nueva Validación para los selects de inasistencia ---
    // Esta validación solo aplica si el registro de inasistencia es obligatorio
    if ($this->tipoGrupo->registrar_inasistencia && $this->tipoGrupo->inasistencia_obligatoria) {
        // Itera sobre la colección de personas que se muestra en la vista
        foreach ($this->personas as $persona) {
            // Verifica si el toggle de asistencia para esta persona está en 'false' (inasistente)
            // y si existe una entrada para esta persona en togglesAsistencia.
            if (isset($this->togglesAsistencia[$persona->id]) && $this->togglesAsistencia[$persona->id] === false) {
                $campoInasistencia = "selectInasistencias.{$persona->id}";

                $rules[$campoInasistencia] = 'required';
                $messages[$campoInasistencia . '.required'] = 'Debe seleccionar un motivo de inasistencia.';

                // Validación para la observación (si el motivo seleccionado la requiere)
                $selectedTipoInasistenciaId = $this->selectInasistencias[$persona->id] ?? null;
                if ($selectedTipoInasistenciaId && isset($this->tiposInasistenciaConObservacionObligatoria[$selectedTipoInasistenciaId])) {
                    $campoObservacion = "observacionesInasistencia.{$persona->id}";
                    $rules[$campoObservacion] = 'required'; // Ajusta max según tu BD
                    $messages[$campoObservacion . '.required'] = 'Describe el motivo.';
                }
            }
        }
    }

    if($rules)
    {
      $this->validate($rules, $messages);
    }

    /// aqui voy a construir todos los arreglos con los ids que necesito para clasificar o no los tipos de asistentes
    $idsEncargados = []; //   $array_ids_todos_encargados=array();
    $idsEncargadosAsistieron = []; //$array_ids_encargados_si_asistieron=array();
    $idsMiembros = []; // $array_ids_todos_asistentes=array();
    $idsMiebrosAsistieron = [];// $array_ids_asistentes_asistieron=array();
    $fechaReporte = $this->reporte->fecha;
    $cantidadHombresEncargado=0; // Me cuantifica la cantidad de encargados hombres que asistieron al reporte.
		$cantidadMujeresEncargado=0; // Me cuantifica la cantidad de encargados mujeres que asistieron al reporte.
    $cantidadInasistencias=0; // Esta variable acumula las cantidad de  inasistencias
		$cantidadAsistencias=0; // Esta variable acumula las asistencias

    // Guardar asistencia encargados
    if ($this->reporte->informacion_encargado_grupo)
    {
        $dataEncargados = json_decode(json_encode($this->reporte->informacion_encargado_grupo), true); // Clonar como array PHP
        $sumarEncargadoAsistencia =  $this->configuracion->sumar_encargado_asistencia_grupo;

        foreach ($dataEncargados as &$encargado) {
            $encargadoId = $encargado['id'];
            $encargado = User::select('id','genero')->find($encargadoId);

            if($this->togglesEncargadosAsistencia[$encargadoId] == true )
            {
              // asistio
              $encargado['asistio'] = true;
              $idsEncargadosAsistieron[] = $encargadoId;

              if($sumarEncargadoAsistencia)
              {
                if(isset($encargado))
                {
                  if($encargado->genero==0)
                  $cantidadHombresEncargado+=1;
                  else
                  $cantidadMujeresEncargado+=1;
                }
                $cantidadAsistencias+=1;
              }

            } else {
              $encargado['asistio'] = false;

              if($sumarEncargadoAsistencia)
              $cantidadInasistencias+=1;

            }

            $idsEncargados[] = $encargadoId;

        }
        $this->reporte->informacion_encargado_grupo = $dataEncargados; // Asignar el array modificado
        $this->reporte->save();
    }

    // Guardar la asistencia de los miembros
    foreach ($this->togglesAsistencia as $miembroId => $asistio)
    {
      // $miembroId contendrá la ID del asistente al grupo
      // $asistio contendrá el valor del checkbox para ese asistente (true o false)

      $asistencia = $this->reporte->usuarios()->withTrashed()->where('users.id', $miembroId)->first();
      // Si tiene asistencia creada
      if( $asistencia )
      {
        // hay cambio
        if($asistencia->pivot->asistio != $asistio)
        {
          $asistencia->pivot->asistio = $asistio;
          if($asistio == false)
          {
            // cambio de asistencia true a false
            $cantidadInasistencias+=1;

            // falta el motivo de inasistencia y observacion
            $asistencia->pivot->tipo_inasistencia_id =  $this->selectInasistencias[$miembroId];
            $asistencia->pivot->observaciones= $this->observacionesInasistencia[$miembroId] ? $this->observacionesInasistencia[$miembroId] : '';

            //Actualizo las fechas de ultimo_reporte_grupo
            if( $this->reporte->fecha == Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') ){
              $asistencia->ultimo_reporte_grupo=$asistencia->ultimo_reporte_grupo_auxiliar;
            }

          }else{
            // cambio de asistencia false a true
            $asistencia->pivot->tipo_inasistencia_id = NULL;
            $asistencia->pivot->observaciones= "";
            $cantidadAsistencias+=1;

            if(Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') < $this->reporte->fecha ){
              $asistencia->ultimo_reporte_grupo_auxiliar = $asistencia->ultimo_reporte_grupo;
              $asistencia->ultimo_reporte_grupo = $this->reporte->fecha;
            }elseif($this->reporte->fecha < Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') && $this->reporte->fecha > Carbon::parse($asistencia->ultimo_reporte_grupo_auxiliar)->format('Y-m-d')){
              $asistencia->ultimo_reporte_grupo_auxiliar=$this->reporte->fecha;
            }

          }
        }else{
          $asistencia->pivot->asistio = $asistio;
          if($asistio == false)
          {
            $cantidadInasistencias+=1;

            // Cambio el motivo de inasistencia y observacion
            $asistencia->pivot->tipo_inasistencia_id =  $this->selectInasistencias[$miembroId];
            $asistencia->pivot->observaciones = $this->observacionesInasistencia[$miembroId] ? $this->observacionesInasistencia[$miembroId] : '';

            //Actualizo las fechas de ultimo_reporte_grupo
            if( $this->reporte->fecha == Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') ){
              $asistencia->ultimo_reporte_grupo=$asistencia->ultimo_reporte_grupo_auxiliar;
            }

          }else{
            $cantidadAsistencias+=1;

            //Actualizo las fechas de ultimo_reporte_grupo
            if(Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') < $this->reporte->fecha ){
              $asistencia->ultimo_reporte_grupo_auxiliar = $asistencia->ultimo_reporte_grupo;
              $asistencia->ultimo_reporte_grupo = $this->reporte->fecha;
            }elseif($this->reporte->fecha < Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') && $this->reporte->fecha > Carbon::parse($asistencia->ultimo_reporte_grupo_auxiliar)->format('Y-m-d')){
              $asistencia->ultimo_reporte_grupo_auxiliar=$this->reporte->fecha;
            }


          }
        }
        $asistencia->save();
        $asistencia->pivot->save();

      }else{
        // No tiene asistencia creada

        if($asistio)
        {
          // marcado como que asistio
          $this->reporte->usuarios()->attach($miembroId, ['asistio' => 'true']);
          $asistencia = $this->reporte->usuarios()->where('users.id', $miembroId)->first();
          $cantidadAsistencias+=1;

          //Actualizo las fechas de ultimo_reporte_grupo
          if(Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') < $this->reporte->fecha ){
            $asistencia->ultimo_reporte_grupo_auxiliar = $asistencia->ultimo_reporte_grupo;
            $asistencia->ultimo_reporte_grupo = $this->reporte->fecha;
          }elseif($this->reporte->fecha < Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') && $this->reporte->fecha > Carbon::parse($asistencia->ultimo_reporte_grupo_auxiliar)->format('Y-m-d')){
            $asistencia->ultimo_reporte_grupo_auxiliar=$this->reporte->fecha;
          }
        }else {
          // marcado como que no asistio
          $this->reporte->usuarios()->attach($miembroId, [
            'asistio' => 'false',

          ]);
          $asistencia = $this->reporte->usuarios()->withTrashed()->where('users.id', $miembroId)->first();
          $cantidadInasistencias+=1;

          // falta el motivo de inasistencia y observacion
          $asistencia->pivot->tipo_inasistencia_id =  $this->selectInasistencias[$miembroId];
          $asistencia->pivot->observaciones= $this->observacionesInasistencia[$miembroId] ? $this->observacionesInasistencia[$miembroId] : '';

          //Actualizo las fechas de ultimo_reporte_grupo
          if( $this->reporte->fecha == Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') ){
            $asistencia->ultimo_reporte_grupo=$asistencia->ultimo_reporte_grupo_auxiliar;
          }

        }
        $asistencia->save();
        $asistencia->pivot->save();
      }

    }

    $idsMiembros = $this->reporte->usuarios()->withTrashed()->select('users.id')->pluck('users.id')->toArray();
    $idsMiebrosAsistieron = $this->reporte->usuarios()->withTrashed()->wherePivot("asistio", "=", TRUE)->select('users.id')->pluck('users.id')->toArray();

    // guardo las ofrendas genericas
    if($this->reporte->aprobado === null)
    {
      foreach ($this->inputsOfrendasGenerica as $tipoOfrendaId => $datosOfrenda)
      {
        $ofrendaGenerica= $this->reporte->ofrendas()->where('tipo_ofrenda_id', '=', $tipoOfrendaId )->first();

        if( $ofrendaGenerica )
        {
          if( $datosOfrenda['valor'] > 0)
          {
            $ofrendaGenerica->valor = $datosOfrenda['valor'];
            // actualiza las ofrendas automaticamente si no hay sistema de aprobacion
            if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
            {
              $ofrendaGenerica->valor_real = $datosOfrenda['valor'];
            }
            $ofrendaGenerica->save();

            // consulto el ingreso el ingreso
            $ingreso = Ingreso::where('ofrenda_id', $ofrendaGenerica->id)->first();

            if($ingreso)
            {
              $ingreso->valor = $ofrendaGenerica->valor;
              if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
              {
                $ingreso->valor_real = $ofrendaGenerica->valor;
              }
              $ingreso->save();
            }


          }else{
            $this->reporte->ofrendas()->detach($ofrendaGenerica->id);
            $ofrendaGenerica->delete();

            // elimino los ingresos automaticamente si no hay sistema de aprobacion
            if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
            {
              $ingreso = Ingreso::where('ofrenda_id', $ofrendaGenerica->id)->first();
              $ingreso->delete();
            }

          }



        }else{

          if( $datosOfrenda['valor'] > 0)
          {
            $ofrendaGenericaNueva= new Ofrenda;
            $ofrendaGenericaNueva->tipo_ofrenda_id = $tipoOfrendaId;
            $ofrendaGenericaNueva->valor = $datosOfrenda['valor'];
            $ofrendaGenericaNueva->fecha = $this->reporte->fecha;
            $ofrendaGenericaNueva->ingresada_por = 1;
            $ofrendaGenericaNueva->moneda_id = $this->moneda->id;
            $ofrendaGenericaNueva->observacion = '';
            $ofrendaGenericaNueva->save();

            //guardo en la tabla pivot
            $this->reporte->ofrendas()->attach($ofrendaGenericaNueva->id);

            // creo el ingreso
            $ingreso = new Ingreso;
            $ingreso->fecha = $this->reporte->fecha;
            $ingreso->nombre = 'Ingreso como '.$ofrendaGenericaNueva->tipoOfrenda->nombre.' reporte ofrenda general';
            $ingreso->identificacion = 'Grupo: '.$this->grupo->nombre;
            $ingreso->tipo_identificacion = 'Grupo: '.$this->grupo->nombre;
            $ingreso->telefono = 'Grupo: '.$this->grupo->nombre;
            $ingreso->direccion = 'Grupo: '.$this->grupo->nombre;
            $ingreso->valor = $ofrendaGenericaNueva->valor;

            // aprueba las ofrendas automaticamente si no hay sistema de aprobacion
            if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
            {
              $ofrendaGenericaNueva->valor_real = $datosOfrenda['valor'];
              $ofrendaGenericaNueva->save();
              $ingreso->valor_real = $ofrendaGenericaNueva->valor;
            }

            $ingreso->descripcion ='Registro ofrenda general reporte grupo '.$this->reporte->id;
            $ingreso->tipo_ofrenda_id = $ofrendaGenericaNueva->tipo_ofrenda_id;
            $ingreso->caja_finanzas_id = 1;
            $ingreso->sede_id = $this->grupo->sede_id;
            $ingreso->user_id = 0;
            $ingreso->ofrenda_id = $ofrendaGenericaNueva->id;
            $ingreso->moneda_id = $this->moneda->id;
            $ingreso->ingreso_por_grupo = true;
            $ingreso->save();

          }
        }

      }
    }

    // guardo las ofrendas especificas
    if($this->reporte->aprobado === null)
    {
      // Ahora, guardar las ofrendas con la nueva estructura
      foreach ($this->ofrendasPorPersona as $userId => $ofrendas) {

        foreach ($ofrendas as $tipoOfrendaId => $ofrendaData) {
          $ofrendaActual= $this->reporte
          ->ofrendas()
          ->where('user_id', $userId)
          ->where('tipo_ofrenda_id', '=', $tipoOfrendaId )
          ->first();

          if( $ofrendaActual )
          {
            if( $ofrendaData['valor'] > 0)
            {
              $ofrendaActual->valor = $ofrendaData['valor'];
              // actualiza las ofrendas automaticamente si no hay sistema de aprobacion
              if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
              {
                $ofrendaActual->valor_real = $ofrendaData['valor'];
              }
              $ofrendaActual->save();

              // consulto el ingreso el ingreso
              $ingreso = Ingreso::where('ofrenda_id', $ofrendaActual->id)->first();

              if($ingreso)
              {
                $ingreso->valor = $ofrendaActual->valor;
                if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
                {
                  $ingreso->valor_real = $ofrendaActual->valor;
                }
                $ingreso->save();
              }


            }else{
              $this->reporte->ofrendas()->detach($ofrendaActual->id);
              $ofrendaActual->delete();

              // elimino los ingresos automaticamente si no hay sistema de aprobacion
              if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
              {
                $ingreso = Ingreso::where('ofrenda_id', $ofrendaActual->id)->first();
                $ingreso->delete();
              }

            }
          }else{

            if( $ofrendaData['valor'] > 0)
            {
              $ofrendaNueva= new Ofrenda;
              $ofrendaNueva->tipo_ofrenda_id = $tipoOfrendaId;
              $ofrendaNueva->valor = $ofrendaData['valor'];
              $ofrendaNueva->fecha = $this->reporte->fecha;
              $ofrendaNueva->ingresada_por = 1;
              $ofrendaNueva->moneda_id = $this->moneda->id;
						  $ofrendaNueva->user_id= $userId;
              $ofrendaNueva->observacion = '';
              $ofrendaNueva->save();

              //guardo en la tabla pivot
              $this->reporte->ofrendas()->attach($ofrendaNueva->id);

              $dador = User::withTrashed()->find($userId);
              // creo el ingreso
              $ingreso = new Ingreso;
              $ingreso->fecha = $this->reporte->fecha;
              $ingreso->nombre = $dador->nombre(3);
              $ingreso->identificacion = $dador->identificacion;
              $ingreso->tipo_identificacion = $dador->tipo_identificacion_id;
              $ingreso->telefono = $dador->telefono_movil;
              $ingreso->direccion = $dador->direccion;
              $ingreso->valor = $ofrendaNueva->valor;

              // aprueba las ofrendas automaticamente si no hay sistema de aprobacion
              if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
              {
                $ofrendaNueva->valor_real = $ofrendaData['valor'];
                $ofrendaNueva->save();
                $ingreso->valor_real = $ofrendaNueva->valor;
              }

              $ingreso->descripcion ='Registro ofrenda individual reporte grupo '.$this->reporte->id;
              $ingreso->tipo_ofrenda_id = $ofrendaNueva->tipo_ofrenda_id;
              $ingreso->caja_finanzas_id = 1;
              $ingreso->sede_id = $this->grupo->sede_id;
              $ingreso->user_id = $dador->id;
              $ingreso->ofrenda_id = $ofrendaNueva->id;
              $ingreso->moneda_id = $this->moneda->id;
              $ingreso->ingreso_por_grupo = true;
              $ingreso->save();

            }
          }

        }
      }


    }

    // guardo informacion de las clasificaciones
    $clasificacionesReporte = $this->reporte->clasificaciones; //$this->reporte->clasificaciones()->withTrashed()->get();

    foreach($clasificacionesReporte as $clasificacion)
    {
      $sumatoriaAsistentes=0;
			$tiposUsuariosDeLaClasificacion = $clasificacion->tipoUsuarios;

      foreach ($tiposUsuariosDeLaClasificacion as $tipoUsuarioDeLaClasificacion)
			{
        //si es TRUE "incluye los asistentes que asistieron y los que no", si es FALSE "Solo los que asistieron al reporte
				if($clasificacion->todos_los_asistentes)
        {
          if ($clasificacion->sumar_asistencias_encargados == true && $clasificacion->clasificacion_encargado_por_clasificacion_individual == true )
          {
            $idsResultante = array_merge($idsEncargados,$idsMiembros);
          }else{
            $idsResultante = $idsMiembros;
          }
					$asistentes = User::withTrashed()->whereIn('id', $idsResultante)->where('users.tipo_usuario_id', '=', $tipoUsuarioDeLaClasificacion->id);

				}else{
					if ($clasificacion->sumar_asistencias_encargados == true && $clasificacion->clasificacion_encargado_por_clasificacion_individual == true )
					{
						$idsResultante = array_merge($idsEncargadosAsistieron,$idsMiebrosAsistieron);
					}else{
            $idsResultante = $idsMiebrosAsistieron;
					}
					$asistentes = User::withTrashed()->whereIn('id', $idsResultante)->where('users.tipo_usuario_id', '=', $tipoUsuarioDeLaClasificacion->id);
				}

        // MAYORES
        if (isset($tipoUsuarioDeLaClasificacion->pivot->edad_minima)) {
          $edadMinima = $tipoUsuarioDeLaClasificacion->pivot->edad_minima;
          $fechaLimiteMinima = Carbon::parse($fechaReporte)->subYears($edadMinima)->toDateString();
          $asistentes = $asistentes->where('users.fecha_nacimiento', '<=', $fechaLimiteMinima);
        }

        // MENORES
        if (isset($tipoUsuarioDeLaClasificacion->pivot->edad_maxima)) {
          $edadMaxima = $tipoUsuarioDeLaClasificacion->pivot->edad_maxima;
          $fechaLimiteMaxima = Carbon::parse($fechaReporte)->subYears($edadMaxima)->toDateString();
          $asistentes = $asistentes->where('users.fecha_nacimiento', '>=', $fechaLimiteMaxima);
        }

				//FECHA INGRESO == FECHA REPORTE
				if($tipoUsuarioDeLaClasificacion->pivot->fecha_ingreso_igual_fecha_reporte==TRUE)
				{
					$asistentes= $asistentes->where('users.fecha_ingreso', '=', $fechaReporte);
				}

				// GENERO
				if(isset($clasificacion->genero))
				{
					$asistentes= $asistentes->where('users.genero', '=', $clasificacion->genero);
				}

				if(isset($tipoUsuarioDeLaClasificacion->pivot->paso_id))
				{
					$pasoId= $tipoUsuarioDeLaClasificacion->pivot->paso_id;

					if(isset($tipoUsuarioDeLaClasificacion->pivot->estado_paso))
					{
						$estadoPaso= $tipoUsuarioDeLaClasificacion->pivot->estado_paso;

						if($tipoUsuarioDeLaClasificacion->pivot->fecha_paso_igual_fecha_reporte==TRUE)
						{
							$asistentes= $asistentes->whereHas('pasosCrecimiento', function($q) use ($pasoId,$fechaReporte,$estadoPaso){
								$q->where('pasos_crecimiento.id', '=', $pasoId)
								->where('crecimiento_usuario.estado_id', '=', $estadoPaso)
								->whereDate('crecimiento_usuario.fecha', '=', $fechaReporte);
							})->get();

						}else{
							$asistentes= $asistentes->whereHas('pasosCrecimiento', function($q) use ($pasoId,$estadoPaso){
								$q->where('pasos_crecimiento.id', '=', $pasoId)
								->where('crecimiento_usuario.estado_id', '=', $estadoPaso);
							})->get();
						}
					}else
					{
						$asistentes= $asistentes->whereHas('pasosCrecimiento', function($q) use ($pasoId){
								$q->where('pasos_crecimiento.id', '=', $pasoId);
							})->get();
					}

				}

				$sumatoriaAsistentes = $sumatoriaAsistentes+$asistentes->count();
			}

      // sumatorias adicionales
      if($clasificacion->tiene_sumatoria_adicional)
      {
        $sumatoriaAsistentes = $this->inputsSumatoriasAdiccionales[$clasificacion->id]['valor'];
      }

      // calcula cantidad asistencia encargados en proceso normal
      if ($clasificacion->sumar_asistencias_encargados == true && !$clasificacion->clasificacion_encargado_por_clasificacion_individual) {
        if (isset($clasificacion->genero)) {
            $sumatoriaAsistentes += ($clasificacion->genero == 1) ? $cantidadMujeresEncargado : $cantidadHombresEncargado;
        } else {
            $sumatoriaAsistentes += $cantidadHombresEncargado + $cantidadMujeresEncargado;
        }
      }

			// sumar al total de asistencias
			if($clasificacion->sumar_al_total_de_asistencias)
			{
				$cantidadAsistencias+=$sumatoriaAsistentes;
			}

      $clasificacion->pivot->cantidad = $sumatoriaAsistentes;
      $clasificacion->pivot->save();
    }

      $arrayEncargados = [];

    if (isset($this->grupo)) {
      // Obtener los IDs de usuarios excluidos del grupo
      $idsUsuariosExcluidos = $this->grupo->gruposExcluidos()->pluck('users.id')->toArray();

      // Obtener todos los encargados del grupo
      $encargados = $this->grupo->encargados()->get();

      foreach ($encargados as $encargado) {
        // Obtener los IDs de los líderes del encargado que no están excluidos
        $lideresIds = $encargado->lideres()->whereNotIn('users.id', $idsUsuariosExcluidos)->select('users.id')->pluck('users.id')->toArray();

        // Fusionar los IDs de líderes en el array principal, evitando duplicados
        $arrayEncargados = array_unique(array_merge($arrayEncargados, $lideresIds));
      }
    }

		$this->reporte->encargados_ascendentes = $arrayEncargados;
		$this->reporte->informacion_del_grupo = Grupo::select('nombre', 'codigo', 'dia', 'tipo_grupo_id', 'telefono', 'direccion')->find($this->grupo->id);

		$this->reporte->cantidad_asistencias = $cantidadAsistencias;
		$this->reporte->cantidad_inasistencias = $cantidadInasistencias;

    if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
    {
      //fecha_aprobacion
      $this->reporte->fecha_aprobacion = Carbon::now()->toDateTimeString();
    }

		$this->reporte->finalizado=TRUE;
		$this->reporte->save();

		if( $this->reporte->fecha > $this->grupo->ultimo_reporte_grupo && $this->grupo->ultimo_reporte_grupo >= $this->grupo->ultimo_reporte_grupo_auxiliar)
		{
			$this->grupo->ultimo_reporte_grupo_auxiliar = $this->grupo->ultimo_reporte_grupo;
			$this->grupo->ultimo_reporte_grupo = $this->reporte->fecha;
			$this->grupo->save();
		}

		if($this->grupo->ultimo_reporte_grupo_auxiliar == $this->reporte->fecha)
		{
			$this->grupo->save();
		}

		if($this->grupo->ultimo_reporte_grupo == $this->reporte->fecha)
		{
			$this->grupo->save();
		}

    return redirect()->route('reporteGrupo.mensajeReporteFinalizado', $this->reporte);

  }

  public function render()
  {
      return view('livewire.reporte-grupos.asistencias');
  }
}
