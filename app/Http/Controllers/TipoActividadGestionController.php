<?php

namespace App\Http\Controllers;

use App\Models\TipoActividad;
use Illuminate\Http\Request;
use App\Models\Configuracion;

class TipoActividadGestionController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $tiposActividad = TipoActividad::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('descripcion', 'like', '%' . $buscar . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(12);

        $configuracion = Configuracion::find(1);

        return view('contenido.paginas.configuracion.actividades.tipos.index', [
            'tiposActividad' => $tiposActividad,
            'configuracion' => $configuracion,
            'buscar' => $buscar
        ]);
    }

    public function nuevo()
    {
        return view('contenido.paginas.configuracion.actividades.tipos.crear');
    }

    public function crear(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'color' => 'nullable|string|max:30',
        ]);

        $tipoActividad = new TipoActividad($validatedData);

        // Campos booleanos
        $tipoActividad->requiere_inscripcion = $request->has('requiere_inscripcion');
        $tipoActividad->unica_compra = $request->has('unica_compra');
        $tipoActividad->multiples_compras = $request->has('multiples_compras');
        $tipoActividad->unica_inscripcion = $request->has('unica_inscripcion');
        $tipoActividad->multiples_inscripciones = $request->has('multiples_inscripciones');
        $tipoActividad->requiere_inicio_sesion = $request->has('requiere_inicio_sesion');
        $tipoActividad->permite_abonos = $request->has('permite_abonos');
        $tipoActividad->es_gratuita = $request->has('es_gratuita');
        $tipoActividad->tipo_escuelas = $request->has('tipo_escuelas');
        $tipoActividad->inscripcion_parientes = $request->has('inscripcion_parientes');
        $tipoActividad->aplicar_restriccion_menores = $request->has('aplicar_restriccion_menores');
        $tipoActividad->solo_menores_de_edad = $request->has('solo_menores_de_edad');

        $tipoActividad->save();

        return redirect()
            ->route('gestionar-tipos-de-actividad.editar', $tipoActividad->id)
            ->with('success', 'Tipo de actividad "' . $tipoActividad->nombre . '" creado correctamente.');
    }

    public function editar(TipoActividad $tipoActividad)
    {
        return view('contenido.paginas.configuracion.actividades.tipos.editar', [
            'tipoActividad' => $tipoActividad
        ]);
    }

    public function actualizar(Request $request, TipoActividad $tipoActividad)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'color' => 'nullable|string|max:30',
        ]);

        $tipoActividad->fill($validatedData);

        // Campos booleanos
        $tipoActividad->requiere_inscripcion = $request->has('requiere_inscripcion');
        $tipoActividad->unica_compra = $request->has('unica_compra');
        $tipoActividad->multiples_compras = $request->has('multiples_compras');
        $tipoActividad->unica_inscripcion = $request->has('unica_inscripcion');
        $tipoActividad->multiples_inscripciones = $request->has('multiples_inscripciones');
        $tipoActividad->requiere_inicio_sesion = $request->has('requiere_inicio_sesion');
        $tipoActividad->permite_abonos = $request->has('permite_abonos');
        $tipoActividad->es_gratuita = $request->has('es_gratuita');
        $tipoActividad->tipo_escuelas = $request->has('tipo_escuelas');
        $tipoActividad->inscripcion_parientes = $request->has('inscripcion_parientes');
        $tipoActividad->aplicar_restriccion_menores = $request->has('aplicar_restriccion_menores');
        $tipoActividad->solo_menores_de_edad = $request->has('solo_menores_de_edad');

        $tipoActividad->save();

        return redirect()
            ->route('gestionar-tipos-de-actividad.editar', $tipoActividad->id)
            ->with('success', 'Tipo de actividad "' . $tipoActividad->nombre . '" actualizado correctamente.');
    }
}
