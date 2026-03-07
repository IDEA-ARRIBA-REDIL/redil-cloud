<?php

namespace App\Exports;

use App\Helpers\Helpers;
use App\Models\Peticion;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use Carbon\Carbon;

class PeticionesExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */


    public function __construct(string $tipo, $parametrosBusqueda, $camposPeticiones, $arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra)
    {
      if($tipo == 'sin-responder'){
        $this->tipo = 1;
      }elseif($tipo == 'finalizadas'){
        $this->tipo = 2;
      }elseif($tipo == 'con-seguimiento'){
        $this->tipo = 3;
      }

      $this->camposPeticiones = $camposPeticiones;
      $this->arrayCamposInfoPersonal = $arrayCamposInfoPersonal;
      $this->arrayPasosCrecimiento = $arrayPasosCrecimiento;
      $this->arrayDatosCongregacionales = $arrayDatosCongregacionales;
      $this->arrayCamposExtra = $arrayCamposExtra;
      $this->parametrosBusqueda = $parametrosBusqueda;

    }


    public function view(): View
    {
      $peticiones = [];
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $campos = $this->camposPeticiones->pluck('value')->toArray();
      array_push($campos,"id","user_id");

      if ( $rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio') )
      {
        if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
          $peticiones = auth()->user()->misPeticiones();
        }

        if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
          $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*','users.foto','users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre','users.segundo_nombre', 'users.primer_apellido')
          ->get();
        }

      }

      if($this->tipo == 'sin-responder'){
        $peticiones = $peticiones->where('estado', 1);
      }elseif($this->tipo == 'finalizadas'){
        $peticiones = $peticiones->where('estado', 2);
      }elseif($this->tipo == 'con-seguimiento'){
        $peticiones = $peticiones->where('estado', 3);
      }

      // Filtro por fechas
      $filtroFechaIni = $this->parametrosBusqueda && $this->parametrosBusqueda->filtroFechaIni ? $this->parametrosBusqueda->filtroFechaIni : Carbon::now()->firstOfYear()->format('Y-m-d');
      $filtroFechaFin = $this->parametrosBusqueda && $this->parametrosBusqueda->filtroFechaFin ? $this->parametrosBusqueda->filtroFechaFin : Carbon::now()->format('Y-m-d');
      $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);


      // Filtro por persona
      if ($this->parametrosBusqueda && isset($this->parametrosBusqueda->persona_id))
      {
        $peticiones = $peticiones->whereIn('user_id', $this->parametrosBusqueda->persona_id);
      }

      // filtro por tipo peticiones
      if ($this->parametrosBusqueda && isset($this->parametrosBusqueda->filtroTipoPeticiones))
      {
        $peticiones = $peticiones->whereIn('tipo_peticion_id', $this->parametrosBusqueda->filtroTipoPeticiones);
      }

      // Busqueda por palabra clave
      if ($this->parametrosBusqueda &&  isset($this->parametrosBusqueda->buscar)) {
        $buscar = htmlspecialchars($this->parametrosBusqueda->buscar);
        $buscar = Helpers::sanearStringConEspacios($buscar);
        $buscar = str_replace(["'"], '', $buscar);
        $buscar_array = explode(' ', $buscar);

        foreach ($buscar_array as $palabra) {
          $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
            $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra);

            return $respuesta;
          });
        }
      }

      $peticiones = $peticiones->toQuery()->get($campos);
      $peticiones->map(function ($peticion) {

        if($peticion->estado)
        $peticion->estado = Helpers::estadoPeticion($peticion->estado);

        if($peticion->autor_creacion_id)
        {
          // usuarioCreacion
          $usuarioCreacion = $peticion->autorCreacion()->withTrashed()->select('id','primer_nombre','segundo_nombre', 'primer_apellido')->first();
          $peticion->usuarioCreacion = ($usuarioCreacion && $peticion->user_id != $usuarioCreacion->id)
          ? $usuarioCreacion->nombre(3)
          : 'Autogestión';
        }else{
          $peticion->usuarioCreacion = 'Autogestión';
        }

        if($peticion->pais_id)
        {
          $peticion->paisNombre = $peticion->pais_id ? $peticion->pais->nombre : 'No indicado';
        }else{
          $peticion->paisNombre = 'No indicado';
        }
      });

      return view('contenido.paginas.peticiones.exportar.exportarPeticiones', [
        'peticiones' => $peticiones,
        'tipo' => $this->tipo,
        'camposPeticiones' => $this->camposPeticiones,
        'arrayCamposInfoPersonal' => $this->arrayCamposInfoPersonal,
        'arrayPasosCrecimiento' => $this->arrayPasosCrecimiento,
        'arrayDatosCongregacionales' => $this->arrayDatosCongregacionales,
        'arrayCamposExtra' => $this->arrayCamposExtra
      ]);
    }
}
