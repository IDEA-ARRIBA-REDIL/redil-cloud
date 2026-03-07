<?php

namespace App\Http\Controllers;

use App\Models\CampoRuedaDeLaVida;
use App\Models\CampoSeccionRv;
use App\Models\Configuracion;
use App\Models\ConfiguracionRv;
use App\Models\HabitosRv;
use App\Models\Metas;
use App\Models\RuedaDeLaVidaUser;
use App\Models\SeccionRv;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RuedaDeLaVidaController extends Controller
{
    //

    public function gestor()
    {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

      // verificar si cumple el permiso
      $rolActivo->verificacionDelPermiso('rueda_de_la_vida.item_rueda_de_la_vida');

        $usuario = auth()->user();
        $ruedasDeLaVida = RuedaDeLaVidaUser::where('usuario_id', $usuario->id)->orderBy('created_at','asc')->paginate(10);

        if ($ruedasDeLaVida->isNotEmpty()) {
            return redirect()->route('ruedaDeLaVida.historial'); // Redirige a la ruta nombrada 'historial'
        } else {
            return redirect()->route('ruedaDeLaVida.bienvenida'); // Redirige a la ruta nombrada 'bienvenida'
        }
    }

    public function historial()
    {
        $usuario = auth()->user();
        $configuracionRv = ConfiguracionRv::first();
        $ruedasDeLaVida = RuedaDeLaVidaUser::where('usuario_id', $usuario->id)->orderBy('created_at','asc')->paginate(10);

        return view(
            'contenido.paginas.rueda-de-la-vida.historial',
            [
                'usuario' => $usuario,
                'configuracionRv' => $configuracionRv,
                'ruedasDeLaVida' => $ruedasDeLaVida
            ]
        );
    }

    public function bienvenida()
    {
        $usuario = auth()->user();
        $configuracionRv = ConfiguracionRv::first();

        $configuracion = Configuracion::first();

        return view(
            'contenido.paginas.rueda-de-la-vida.bienvenida',
            [
                'usuario' => $usuario,
                'configuracionRv' => $configuracionRv,
                'configuracion' => $configuracion

            ]
        );
    }

    public function finalizada()
    {
        $usuario = auth()->user();
        $configuracionRv = ConfiguracionRv::first();
        $configuracion = Configuracion::first();

        return view(
            'contenido.paginas.rueda-de-la-vida.exitosa',
            [
                'usuario' => $usuario,
                'configuracionRv' => $configuracionRv,
                'configuracion' => $configuracion

            ]
        );
    }

    public function resumen(RuedaDeLaVidaUser $rueda)
    {
        $usuario = auth()->user();
        $configuracionRv = ConfiguracionRv::first();
        $seccionesContadorPromedios = SeccionRv::with('campos')->where('tipo_seccion_id', 1)->get();
        $metasRv = Metas::get();
        $habitosMetasRv = HabitosRv::get();


        return view(
            'contenido.paginas.rueda-de-la-vida.resumen',
            [
                'usuario' => $usuario,
                'configuracionRv' => $configuracionRv,
                'seccionesContadorPromedios' => $seccionesContadorPromedios,
                'rueda' => $rueda,
                'metasRv' => $metasRv,
                'habitosMetasRv' => $habitosMetasRv
            ]
        );
    }

    public function nueva()
    {

        $secciones = SeccionRv::orderBy('orden', 'asc')->get();
        $seccionesContadorPromedios = SeccionRv::with('campos')->where('tipo_seccion_id', 1)->get();
        $cantidadTotalSecciones = $secciones->count();
        $configuracion = Configuracion::first();
        $configuracionRv = ConfiguracionRv::first();
        $maximoId = $secciones->last()->id;


        return view(
            'contenido.paginas.rueda-de-la-vida.nueva',
            [
                'secciones' => $secciones,
                'cantidadTotalSecciones' => $cantidadTotalSecciones,
                'configuracion' => $configuracion,
                'configuracionRv' => $configuracionRv,
                'seccionesContadorPromedios' =>  $seccionesContadorPromedios,
                'maximoId' => $maximoId
            ]
        );
    }

    public function crear(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $usuario = User::find($rolActivo->pivot->model_id);
        $fechaHoy = Carbon::now()->format('Y-m-d');



        /// aqui primero se crea una rueda de la vida para poder luego crear las tareas intermedias
        $ruedaDelaVida = new  RuedaDeLaVidaUser;
        $ruedaDelaVida->usuario_id = $usuario->id;
        $ruedaDelaVida->fecha = $fechaHoy;
        $ruedaDelaVida->promedio_general = $request->valorPromedioGeneralOculto;
        $ruedaDelaVida->save();

        //// aqui se guardan los campos seccion de la rueda de la vida
        $secciones = SeccionRv::with('campos')->where('tipo_seccion_id', 1)->orderBy('orden', 'asc')->get();
        $metasRv = Metas::orderBy('id', 'asc')->get();

        foreach ($secciones as $seccion) {
            foreach ($seccion->campos as $campo) {
                if ($campo->abierto == true) {

                    $ruedaDelaVida->campos()->attach($campo->id, [
                        'valor' => $request->input("campo-" . $campo->id . "-seccion-" . $seccion->id),
                        'nombre_campo_abierto' => $request->input("campo-abierto-" . $campo->id . "-seccion" . $seccion->id)
                    ]);
                } else {
                    $ruedaDelaVida->campos()->attach($campo->id, [
                        'valor' => $request->input("campo-" . $campo->id . "-seccion-" . $seccion->id)
                    ]);
                }
            }
        }

        // Guardar las metas y hábitos en las tablas intermedias
        foreach ($metasRv as $meta) {
            $respuesta = $request->input("inputMeta-{$meta->id}");
            if ($respuesta == '') {
                $respuesta = 'no registrado';
            } else {
                $respuesta = $request->input("inputMeta-{$meta->id}");
            }

            $ruedaDelaVida->metas()->attach($meta->id, [ // Usamos $i como ID de la meta
                'valor' => $respuesta
            ]);

            foreach ($meta->habitos as $habito) {
                $respuesta = $request->input("inputHabitoMeta-{$habito->id}");
                if ($respuesta == '') {
                    $respuesta = 'no registrado';
                } else {
                    $respuesta = $request->input("inputHabitoMeta-{$habito->id}");
                }

                $ruedaDelaVida->habitos()->attach($habito->id, [ // Usamos $j como ID del hábito
                    'valor' => $respuesta
                ]);
            }
        }


        return redirect()->route('ruedaDeLaVida.finalizada'); // Redirige a la ruta nombrada 'historial'
    }
}
