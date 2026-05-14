<?php

namespace App\Http\Controllers;

use App\Models\AvanceHabitoRv;
use App\Models\Configuracion;
use App\Models\ConfiguracionRv;
use App\Models\HabitosRv;
use App\Models\HabitoUsuarioRv;
use App\Models\Metas;
use App\Models\MetaUsuarioRv;
use App\Models\RuedaDeLaVidaUser;
use App\Models\SeccionRv;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RuedaDeLaVidaController extends Controller
{
    //

    public function gestor()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // verificar si cumple el permiso
        $rolActivo->verificacionDelPermiso('rueda_de_la_vida.item_rueda_de_la_vida');

        $usuario = auth()->user();
        $ruedasDeLaVida = RuedaDeLaVidaUser::where('usuario_id', $usuario->id)->orderBy('created_at', 'asc')->paginate(10);

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
        $ruedasDeLaVida = RuedaDeLaVidaUser::where('usuario_id', $usuario->id)->orderBy('created_at', 'asc')->paginate(10);

        return view(
            'contenido.paginas.rueda-de-la-vida.historial',
            [
                'usuario' => $usuario,
                'configuracionRv' => $configuracionRv,
                'ruedasDeLaVida' => $ruedasDeLaVida,
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
                'configuracion' => $configuracion,

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
                'configuracion' => $configuracion,

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
        $metasUsuario = MetaUsuarioRv::with(['habitos', 'seccion'])->where('rueda_de_la_vida_id', $rueda->id)->get();

        return view(
            'contenido.paginas.rueda-de-la-vida.resumen',
            [
                'usuario' => $usuario,
                'configuracionRv' => $configuracionRv,
                'seccionesContadorPromedios' => $seccionesContadorPromedios,
                'rueda' => $rueda,
                'metasRv' => $metasRv,
                'habitosMetasRv' => $habitosMetasRv,
                'metasUsuario' => $metasUsuario,
            ]
        );
    }

    public function nueva()
    {
        $secciones = SeccionRv::orderBy('orden', 'asc')->get();
        $seccionesContadorPromedios = SeccionRv::with('campos')->where('tipo_seccion_id', 1)->get();
        $seccionesContador = SeccionRv::where('tipo_seccion_id', 1)->orderBy('orden', 'asc')->get();
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
                'seccionesContadorPromedios' => $seccionesContadorPromedios,
                'seccionesContador' => $seccionesContador,
                'maximoId' => $maximoId,
            ]
        );
    }

    public function crear(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $usuario = User::find($rolActivo->pivot->model_id);
        $fechaHoy = Carbon::now()->format('Y-m-d');

        // / aqui primero se crea una rueda de la vida para poder luego crear las tareas intermedias
        $ruedaDelaVida = new RuedaDeLaVidaUser;
        $ruedaDelaVida->usuario_id = $usuario->id;
        $ruedaDelaVida->fecha = $fechaHoy;
        $ruedaDelaVida->promedio_general = $request->valorPromedioGeneralOculto;
        $ruedaDelaVida->save();

        // // aqui se guardan los campos seccion de la rueda de la vida
        $secciones = SeccionRv::with('campos')->where('tipo_seccion_id', 1)->orderBy('orden', 'asc')->get();

        foreach ($secciones as $seccion) {
            foreach ($seccion->campos as $campo) {
                if ($campo->abierto == true) {
                    $ruedaDelaVida->campos()->attach($campo->id, [
                        'valor' => $request->input('campo-'.$campo->id.'-seccion-'.$seccion->id),
                        'nombre_campo_abierto' => $request->input('campo-abierto-'.$campo->id.'-seccion'.$seccion->id),
                    ]);
                } else {
                    $ruedaDelaVida->campos()->attach($campo->id, [
                        'valor' => $request->input('campo-'.$campo->id.'-seccion-'.$seccion->id),
                    ]);
                }
            }
        }

        // Guardar las metas y hábitos creados dinámicamente por el usuario
        $metasData = $request->input('metas', []);

        foreach ($metasData as $metaData) {
            $nombreMeta = trim($metaData['nombre'] ?? '');
            $seccionRvId = $metaData['seccion_rv_id'] ?? null;

            if ($nombreMeta === '' || ! $seccionRvId) {
                continue;
            }

            $meta = MetaUsuarioRv::create([
                'rueda_de_la_vida_id' => $ruedaDelaVida->id,
                'seccion_rv_id' => $seccionRvId,
                'nombre' => $nombreMeta,
            ]);

            $habitosData = $metaData['habitos'] ?? [];

            foreach ($habitosData as $nombreHabito) {
                $nombreHabito = trim($nombreHabito ?? '');

                if ($nombreHabito === '') {
                    continue;
                }

                HabitoUsuarioRv::create([
                    'meta_usuario_rv_id' => $meta->id,
                    'nombre' => $nombreHabito,
                ]);
            }
        }

        return redirect()->route('ruedaDeLaVida.finalizada'); // Redirige a la ruta nombrada 'historial'
    }

    /**
     * Devuelve el historial de avances de todos los hábitos de una meta en JSON.
     * Cada hábito incluye sus registros de avance ordenados por período ascendente.
     */
    public function avancesHabitos(MetaUsuarioRv $meta): JsonResponse
    {
        $configuracionRv = ConfiguracionRv::first();
        $periodicidad = (int) $configuracionRv->periodicidad;

        // Calcular el inicio del período actual
        $hoy = Carbon::today();
        $periodoActualInicio = $this->calcularPeriodoActual($meta, $periodicidad);

        $habitos = $meta->habitos()->with('avances')->get()->map(function ($habito) use ($periodoActualInicio) {
            $periodoStr = $periodoActualInicio->toDateString();
            $avancePeriodoActual = $habito->avances
                ->first(fn ($a) => $a->periodo_inicio->toDateString() === $periodoStr);

            return [
                'id' => $habito->id,
                'nombre' => $habito->nombre,
                'avances' => $habito->avances->map(fn ($a) => [
                    'periodo_inicio' => $a->periodo_inicio->toDateString(),
                    'puntaje' => $a->puntaje,
                ]),
                'puntaje_actual' => $avancePeriodoActual?->puntaje,
                'periodo_registrado' => ! is_null($avancePeriodoActual),
            ];
        });

        return response()->json([
            'habitos' => $habitos,
            'periodo_actual_inicio' => $periodoActualInicio->toDateString(),
            'ya_registrado' => $habitos->every(fn ($h) => $h['periodo_registrado']),
        ]);
    }

    /**
     * Guarda el avance del período actual para todos los hábitos de una meta.
     * Si el período ya fue registrado, rechaza la operación.
     */
    public function guardarAvanceHabitos(Request $request, MetaUsuarioRv $meta): JsonResponse
    {
        $configuracionRv = ConfiguracionRv::first();
        $periodicidad = (int) $configuracionRv->periodicidad;

        $periodoActualInicio = $this->calcularPeriodoActual($meta, $periodicidad);

        // Validar que el período actual no haya sido registrado para ningún hábito
        $primerHabito = $meta->habitos()->first();
        if ($primerHabito) {
            $yaRegistrado = AvanceHabitoRv::where('habito_usuario_rv_id', $primerHabito->id)
                ->where('periodo_inicio', $periodoActualInicio->toDateString())
                ->exists();

            if ($yaRegistrado) {
                return response()->json(['error' => 'Este período ya fue registrado.'], 422);
            }
        }

        $request->validate([
            'puntajes' => ['required', 'array'],
            'puntajes.*' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        foreach ($request->puntajes as $habitoId => $puntaje) {
            // Verificar que el hábito pertenece a la meta
            $habito = $meta->habitos()->find($habitoId);

            if (! $habito) {
                continue;
            }

            AvanceHabitoRv::create([
                'habito_usuario_rv_id' => $habito->id,
                'puntaje' => $puntaje,
                'periodo_inicio' => $periodoActualInicio->toDateString(),
            ]);
        }

        return response()->json(['success' => true, 'mensaje' => 'Avance registrado correctamente.']);
    }

    /**
     * Devuelve el estado de avances de un hábito individual en JSON.
     * Incluye si el período actual ya fue registrado y el último puntaje para prellenar.
     */
    public function avancesHabito(HabitoUsuarioRv $habito): JsonResponse
    {
        $configuracionRv = ConfiguracionRv::first();
        $periodicidad = (int) $configuracionRv->periodicidad;

        $habito->load(['avances', 'meta.ruedaDeLaVida']);
        $fechaCreacion = Carbon::parse($habito->meta->ruedaDeLaVida->fecha);
        $periodoActualInicio = $this->calcularPeriodoDesde($fechaCreacion, $periodicidad);

        $periodoStr = $periodoActualInicio->toDateString();
        $avancePeriodoActual = $habito->avances
            ->first(fn ($a) => $a->periodo_inicio->toDateString() === $periodoStr);

        $ultimoAvance = $habito->avances->last();

        return response()->json([
            'id' => $habito->id,
            'nombre' => $habito->nombre,
            'avances' => $habito->avances->map(fn ($a) => [
                'periodo_inicio' => $a->periodo_inicio->toDateString(),
                'puntaje' => $a->puntaje,
            ]),
            'periodo_actual_inicio' => $periodoActualInicio->toDateString(),
            'periodo_registrado' => ! is_null($avancePeriodoActual),
            'puntaje_actual' => $avancePeriodoActual?->puntaje,
            'puntaje_anterior' => $ultimoAvance?->puntaje ?? 0,
        ]);
    }

    /**
     * Guarda el avance del período actual para un hábito individual.
     */
    public function guardarAvanceHabito(Request $request, HabitoUsuarioRv $habito): JsonResponse
    {
        $configuracionRv = ConfiguracionRv::first();
        $periodicidad = (int) $configuracionRv->periodicidad;

        $habito->load('meta.ruedaDeLaVida');
        $fechaCreacion = Carbon::parse($habito->meta->ruedaDeLaVida->fecha);
        $periodoActualInicio = $this->calcularPeriodoDesde($fechaCreacion, $periodicidad);

        $yaRegistrado = AvanceHabitoRv::where('habito_usuario_rv_id', $habito->id)
            ->where('periodo_inicio', $periodoActualInicio->toDateString())
            ->exists();

        if ($yaRegistrado) {
            return response()->json(['error' => 'Este período ya fue registrado.'], 422);
        }

        $request->validate([
            'puntaje' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        AvanceHabitoRv::create([
            'habito_usuario_rv_id' => $habito->id,
            'puntaje' => $request->puntaje,
            'periodo_inicio' => $periodoActualInicio->toDateString(),
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Avance registrado correctamente.']);
    }

    /**
     * Calcula la fecha de inicio del período actual en función de la fecha de creación
     * de la rueda y la periodicidad configurada.
     */
    private function calcularPeriodoActual(MetaUsuarioRv $meta, int $periodicidad): Carbon
    {
        return $this->calcularPeriodoDesde(
            Carbon::parse($meta->ruedaDeLaVida->fecha),
            $periodicidad
        );
    }

    /**
     * Lógica base del cálculo de período a partir de una fecha de inicio.
     */
    private function calcularPeriodoDesde(Carbon $fechaCreacion, int $periodicidad): Carbon
    {
        $diasTranscurridos = $fechaCreacion->diffInDays(Carbon::today());
        $periodosCompletos = (int) floor($diasTranscurridos / $periodicidad);

        return $fechaCreacion->copy()->addDays($periodosCompletos * $periodicidad);
    }
}
