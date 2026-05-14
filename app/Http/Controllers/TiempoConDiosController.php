<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\CampoTiempoConDios;
use App\Models\Configuracion;
use App\Models\SeccionTiempoConDios;
use App\Models\TiempoConDios;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;

class TiempoConDiosController extends Controller
{
    public function historial(Request $request)
    {
        
        $usuario = auth()->user();
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
                ->where('estado', 'completado')
                ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
                ->orderBy('fecha', 'desc')
                ->get();
                        
                /*foreach ($tiemposConDios as $tiempoConDios) {
                    $tiempoConDios->fecha = Carbon::parse($tiempoConDios->fecha)->subDay()->format('Y-m-d');
                    $tiempoConDios->save();
                }*/
                


            $meses = Helpers::meses('largo');

            $fechaHoy = Carbon::now()->format('Y-m-d');
            $tiempoConDiosHoy = $usuario->tiemposConDios()->where('fecha', $fechaHoy)->first();
            $estadoHoy = $tiempoConDiosHoy ? $tiempoConDiosHoy->estado : null;

            return view('contenido.paginas.tiempo-con-dios.historial', [
                'usuario' => $usuario,
                'tiemposConDios' => $tiemposConDios,
                'filtroFechaIni' => $filtroFechaIni,
                'filtroFechaFin' => $filtroFechaFin,
                'meses' => $meses,
                'estadoHoy' => $estadoHoy,
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

    public function modoLectura()
    {
        $usuario = auth()->user();

        $fechaHoy = Carbon::now()->format('Y-m-d');
        $tiempoConDiosHoy = $usuario->tiemposConDios()->where('fecha', $fechaHoy)->first();
        
        if ($tiempoConDiosHoy) {
            if ($tiempoConDiosHoy->estado === 'completado') {
                return Redirect::to('pagina-no-encontrada');
            } else {
                return Redirect::route('tiempoConDios.nuevo');
            }
        }

        return view('contenido.paginas.tiempo-con-dios.modo-lectura');
    }

    public function nuevo(Request $request)
    {
        $usuario = auth()->user();

        $fechaHoy = Carbon::now()->format('Y-m-d');
        $tiempoConDiosHoy = $usuario->tiemposConDios()->where('fecha', $fechaHoy)->first();
        
        $pasoActual = 1;
        $respuestasPrevias = [];
        
        // Si no hay borrador, crearlo inmediatamente
        if (!$tiempoConDiosHoy) {
            $modo = $request->query('modo', 'propia');
            $tiempoConDiosHoy = TiempoConDios::create([
                'user_id' => $usuario->id,
                'fecha' => $fechaHoy,
                'estado' => 'en_progreso',
                'paso_actual' => 1,
                'modo' => $modo
            ]);
        }

        $modoLectura = $tiempoConDiosHoy->modo;

        if ($tiempoConDiosHoy) {
            if ($tiempoConDiosHoy->estado === 'completado') {
                return Redirect::to('pagina-no-encontrada');
            } else {
                // Está en progreso
                $pasoActual = $tiempoConDiosHoy->paso_actual;
                // Si el borrador ya tiene plan_lector_id, forzamos modo plan, si no usamos el modo de la DB.
                $modoLectura = $tiempoConDiosHoy->plan_lector_id ? 'plan' : $tiempoConDiosHoy->modo;
                
                // Cargar las respuestas desde la tabla pivote
                $camposGuardados = $tiempoConDiosHoy->campos;
                foreach ($camposGuardados as $campo) {
                    try {
                        $respuestasPrevias[$campo->name_id] = Crypt::decryptString($campo->pivot->valor);
                    } catch (DecryptException $e) {
                        $respuestasPrevias[$campo->name_id] = '';
                    }
                }
            }
        }

        $secciones = SeccionTiempoConDios::orderBy('orden', 'asc')->get();
        $cantidadTotalSecciones = $secciones->count();
        $configuracion = Configuracion::first();

        return view('contenido.paginas.tiempo-con-dios.nuevo', [
            'usuario' => $usuario,
            'secciones' => $secciones,
            'cantidadTotalSecciones' => $cantidadTotalSecciones,
            'configuracion' => $configuracion,
            'pasoActual' => $pasoActual,
            'respuestasPrevias' => $respuestasPrevias,
            'modoLectura' => $modoLectura,
        ]);
    }

    public function crear(Request $request)
    {
        $user = auth()->user();
        $fechaHoy = Carbon::now()->format('Y-m-d');
        
        $tiempoConDiosHoy = $user->tiemposConDios()->where('fecha', $fechaHoy)->first();
        if ($tiempoConDiosHoy && $tiempoConDiosHoy->estado === 'completado') {
            return Redirect::to('pagina-no-encontrada');
        }

        $camposTiempoConDios = CampoTiempoConDios::whereHas('tipo', function ($query) {
            $query->where('es_input', true);
        })->get();

        if (!$tiempoConDiosHoy) {
            $tiempoConDiosHoy = TiempoConDios::create([
                'fecha' => Carbon::now()->format('Y-m-d'),
                'user_id' => auth()->user()->id,
                'estado' => 'en_progreso',
                'paso_actual' => 1
            ]);
        }

        foreach ($camposTiempoConDios as $campo) {
            if (isset($request[$campo->name_id])) {
                $valorEncriptado = Crypt::encryptString($request[$campo->name_id]);
                $tiempoConDiosHoy->campos()->detach($campo->id);
                $tiempoConDiosHoy->campos()->attach($campo->id, ['valor' => $valorEncriptado]);
            }
        }
        $tiempoConDiosHoy->update(['estado' => 'completado']);

        // Marcar día del plan lector como completado
        if ($tiempoConDiosHoy->plan_lector_id && $tiempoConDiosHoy->plan_lector_dia_id) {
            $plan = \App\Models\PlanLector::find($tiempoConDiosHoy->plan_lector_id);
            if ($plan) {
                \Illuminate\Support\Facades\DB::table('plan_lector_dia_users')->updateOrInsert(
                    ['user_id' => $user->id, 'plan_lector_dia_id' => $tiempoConDiosHoy->plan_lector_dia_id],
                    ['fecha_completado' => Carbon::now(), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                );

                // Actualizar progreso total del usuario en el plan
                $totalDias = $plan->dias()->count();
                $completadosCount = \Illuminate\Support\Facades\DB::table('plan_lector_dia_users')
                    ->where('user_id', $user->id)
                    ->whereIn('plan_lector_dia_id', $plan->dias()->pluck('id'))
                    ->count();
                
                $porcentaje = ($totalDias > 0) ? round(($completadosCount / $totalDias) * 100) : 100;

                \Illuminate\Support\Facades\DB::table('plan_lector_users')
                    ->where('plan_lector_id', $plan->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'porcentaje_progreso' => $porcentaje,
                        'estado' => ($porcentaje >= 100) ? 'completado' : 'inscrito'
                    ]);

                $plan->recalcularPromedio();
            }
        }

        $cantidadRachaSemanal = $user->cantidadRachaSemanal();

        $fechaHoy = Carbon::now();
        $diaDeLaSemana = $fechaHoy->dayOfWeekIso;

        return view('contenido.paginas.tiempo-con-dios.tiempo-exitoso', [
            'cantidadRachaSemanal' => $cantidadRachaSemanal,
            'diaDeLaSemana' => $diaDeLaSemana,
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

        $arraySecciones = $campos->pluck('seccion_tiempo_con_dios_id')
            ->unique()
            ->values()
            ->toArray();

        $secciones = SeccionTiempoConDios::whereIn('id', $arraySecciones)->orderBy('orden', 'asc')->get();

        return view('contenido.paginas.tiempo-con-dios.resumen', [
            'tiempoConDios' => $tiempoConDios,
            'secciones' => $secciones,
            'campos' => $campos,
        ]);
    }
}
