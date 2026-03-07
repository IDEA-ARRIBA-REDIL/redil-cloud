<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\PuntoDePago;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Sede;
use stdClass;

class PuntoDePagoController extends Controller
{

  public function listarx(Request $request, $tipo = 'todos')
  {
    $bandera = 0;
    $tagsBusqueda = [];
    $textoBusqueda = '';
    $buscar = "";
    $filtroSede = "";
    $sedes = Sede::select('id', 'nombre')->get();

    $cantidadTodos = PuntoDePago::select('id')->count();
    $cantidadDadosBaja = PuntoDePago::onlyTrashed()->select('id')->count();

    if ($tipo == "todos") {
      $puntosDePago = PuntoDePago::get();
    } else if ($tipo == "dados-de-baja") {
      $puntosDePago = PuntoDePago::onlyTrashed()->get();
    }

    // Busqueda por palabra clave
    if ($request->buscar) {
      $buscar = htmlspecialchars($request->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $puntosDePago = $puntosDePago->filter(function ($puntosDePago) use ($palabra) {
          return false !== stristr(Helpers::sanearStringConEspacios($puntosDePago->nombre), $palabra) ||
            $puntosDePago->id === $palabra;
        });
      }
      $buscar = $request->buscar;
    }


    if ($puntosDePago->count() > 0) {
      $puntosDePago = $puntosDePago->toQuery()->orderBy('id', 'desc')->paginate(12);
    } else {
      $puntosDePago = PuntoDePago::whereRaw('1=2')->paginate(1);
    }



    return view('contenido.paginas.puntos-de-pago.listar', [
      'puntosDePago' => $puntosDePago,
      'sedes' => $sedes,
      'bandera' => $bandera,
      'tagsBusqueda' => $tagsBusqueda,
      'textoBusqueda' => $textoBusqueda,
      'filtroSede' => $filtroSede,
      'cantidadTodos' => $cantidadTodos,
      'cantidadDadosBaja' => $cantidadDadosBaja

    ]);
  }

  public function gestiona2(Request $request, $tipo = 'todos')
  {
    $bandera = 0;
    $tagsBusqueda = [];
    $textoBusqueda = '';
    $buscar = "";
    $filtroSede = "";
    $configuracion = Configuracion::first();

    $sedes = Sede::select('id', 'nombre')->get();
    $query = PuntoDePago::query();

    if ($tipo === 'dados-de-baja') {
      $query->onlyTrashed();
    }

    $buscar = $request->input('buscar'); // Es mejor usar input() para obtener datos de la request

    if ($buscar) {
      $terminoBusquedaLimpio = trim(Helpers::sanearStringConEspacios(str_replace(["'"], '', $buscar)));
      $sqlBuscar = '';

      if (!empty($terminoBusquedaLimpio)) {
        $palabras = explode(' ', $terminoBusquedaLimpio);
        $c = 0;
        foreach ($palabras as $palabra) {
          if ($c != 0)
            $sqlBuscar .= " AND ";
          $sqlBuscar .= "(puntos_de_pago.nombre ILIKE '%$palabra%'";
          $sqlBuscar .= ")";
          $c++;
        }
      }

      $query->where(function ($query) use ($sqlBuscar) {
        $query->whereRaw($sqlBuscar);
      });

      // Crear una tag
      $tag = new stdClass();
      $tag->label = $buscar;
      $tag->field = 'buscar';
      $tag->value = $buscar;
      $tag->fieldAux = '';
      $tagsBusqueda[] = $tag;

      $bandera = 1;
    }

    // Filtrar por sede
    if ($request->sede_id) {
      $query->whereIn('sede_id', $request->sede_id);
      $nombresSeleccionSedes = Sede::whereIn('id', $request->sede_id)->select('nombre', 'id')->get();

      foreach ($nombresSeleccionSedes as $sede) {
        // Crear una tag
        $tag = new stdClass();
        $tag->label = $sede->nombre;
        $tag->field = 'selectSede';
        $tag->value = $sede->id;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }

      $textoBusqueda .= '<b> Sedes: </b>"' . implode(', ', $nombresSeleccionSedes->pluck('nombre')->toArray()) . '"';
      $bandera = 1;
    }


    $puntosDePago = $query->orderBy('id', 'desc')
      ->paginate(12) // Pagina los resultados desde la BD
      ->withQueryString(); // Mantiene los parámetros de la URL (ej: ?buscar=algo&page=2)


    $contadorTodos = PuntoDePago::count();
    $contadorBaja = PuntoDePago::onlyTrashed()->count();

    return view('contenido.paginas.puntos-de-pago.listar', [
      'puntosDePago' => $puntosDePago,
      'sedes' => $sedes,
      'bandera' => $bandera,
      'tagsBusqueda' => $tagsBusqueda,
      'textoBusqueda' => $textoBusqueda,
      'filtroSede' => $filtroSede,
      'contadorTodos' => $contadorTodos,
      'contadorBaja' => $contadorBaja,
      'buscar' => $buscar,
      'configuracion' => $configuracion
    ]);
  }


  public function crear()
  {
    return "cree";
  }

  public function gestionar()
  {
    return view('contenido.paginas.puntos-de-pago.gestionar');
  }

  /**
   * Muestra el informe de transacciones para un punto de pago específico.
   */
  public function verInforme(PuntoDePago $puntoDePago)
  {
      return view('contenido.paginas.puntos-de-pago.informe', compact('puntoDePago'));
  }
}
