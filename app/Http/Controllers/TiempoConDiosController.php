<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\CampoTiempoConDios;
use App\Models\Configuracion;
use App\Models\SeccionTiempoConDios;
use App\Models\TiempoConDios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class TiempoConDiosController extends Controller
{

    public function historial(Request $request)
    {
      $usuario =  auth()->user();
      $existeTiemposConDios = $usuario->tiemposConDios()->first();

      if ($existeTiemposConDios) {
        // Filtro por fechas
        $filtroFechaIni = $request->filtroFechaIni
            ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d')
            : Carbon::now()->subDays(30)->format('Y-m-d');
        $filtroFechaFin = $request->filtroFechaFin
            ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');


        $tiemposConDios = $usuario->tiemposConDios()
        ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
        ->orderBy('fecha','desc')
        ->get();

        $meses = Helpers::meses('largo');

        $fechaHoy = Carbon::now()->format('Y-m-d');
        $tiempoConDiosHoy = $usuario->tiemposConDios()->where('fecha', $fechaHoy)->select('id')->count();


        return view('contenido.paginas.tiempo-con-dios.historial',[
          'usuario' => $usuario,
          'tiemposConDios' => $tiemposConDios,
          'filtroFechaIni' => $filtroFechaIni,
          'filtroFechaFin' => $filtroFechaFin,
          'meses' => $meses,
          'tiempoConDiosHoy' => $tiempoConDiosHoy
        ]);
      } else {
        return redirect()->route('tiempoConDios.bienvenida');
      }
    }

    public function bienvenida()
    {
      $configuracion = Configuracion::first();
      return view('contenido.paginas.tiempo-con-dios.bienvenida', ['configuracion' => $configuracion]);
    }

    public function nuevo()
    {
      $usuario =  auth()->user();

      $fechaHoy = Carbon::now()->format('Y-m-d');
      $tiempoConDiosHoy = $usuario->tiemposConDios()->where('fecha', $fechaHoy)->select('id')->count();
      if ( $tiempoConDiosHoy > 0) {
        return Redirect::to('pagina-no-encontrada');
      }

      $secciones = SeccionTiempoConDios::orderBy('orden','asc')->get();
      $cantidadTotalSecciones = $secciones->count();
      $configuracion = Configuracion::first();

      return view('contenido.paginas.tiempo-con-dios.nuevo', [
        'usuario' => $usuario,
        'secciones' => $secciones,
        'cantidadTotalSecciones' => $cantidadTotalSecciones,
        'configuracion' => $configuracion
      ]);
    }

    public function crear(Request $request)
    {
      $user =auth()->user();
      $fechaHoy = Carbon::now()->format('Y-m-d');
      $tiempoConDiosHoy = $user->tiemposConDios()->where('fecha', $fechaHoy)->select('id')->count();
      if ( $tiempoConDiosHoy > 0) {
        return Redirect::to('pagina-no-encontrada');
      }

      $camposTiempoConDios = CampoTiempoConDios::whereHas('tipo', function ($query) {
          $query->where('es_input', true);
      })->get();

      $tiempoConDios = TiempoConDios::create([
        'fecha' => Carbon::now()->format('Y-m-d'),
        'user_id' =>  auth()->user()->id
      ]);

      if($tiempoConDios)
      {
        $dataToAttach = [];
        foreach ($camposTiempoConDios as $campo) {
            if (isset($request[$campo->name_id])) {
              //  $dataToAttach[$campo->id] = ['valor' => json_encode($request[$campo->name_id])];
                // Encriptar el valor antes de guardar
                $valorEncriptado = Crypt::encryptString($request[$campo->name_id]);
                $tiempoConDios->campos()->attach($campo->id, ['valor' => $valorEncriptado ]);
            }
        }
       // $tiempoConDios->campos()->attach($dataToAttach);
      }

      $cantidadRachaSemanal =  $user->cantidadRachaSemanal();

      $fechaHoy = Carbon::now();
      $diaDeLaSemana = $fechaHoy->dayOfWeekIso;

      return view('contenido.paginas.tiempo-con-dios.tiempo-exitoso', [
        'cantidadRachaSemanal' => $cantidadRachaSemanal,
        'diaDeLaSemana' => $diaDeLaSemana
      ]);

    }

    public function resumen(TiempoConDios $tiempoConDios)
    {

      $campos = CampoTiempoConDios::whereHas('tipo', function ($query) {
        $query->where('es_input', true);
      })->get();

      $campos->map(function ($campo) use ($tiempoConDios) {
        // Busca el registro relacionado en la tabla pivote y asigna el valor
        $campoRelacionado = $tiempoConDios->campos->where('id', $campo->id)->first();
        if ($campoRelacionado) {
            try {
                $campo->valor = Crypt::decryptString($campoRelacionado->pivot->valor);
            } catch (DecryptException $e) {
                // Si falla al desencriptar (ej. datos viejos), mostramos el valor tal cual
                $campo->valor = $campoRelacionado->pivot->valor;
            }
        } else {
            $campo->valor = null;
        }

      });

      $arraySecciones= $campos->pluck('seccion_tiempo_con_dios_id')
      ->unique()
      ->values()
      ->toArray();

      $secciones = SeccionTiempoConDios::whereIn('id',$arraySecciones)->orderBy('orden','asc')->get();

      return view('contenido.paginas.tiempo-con-dios.resumen', [
        'tiempoConDios' => $tiempoConDios,
        'secciones' => $secciones,
        'campos' => $campos
      ]);
    }
}
