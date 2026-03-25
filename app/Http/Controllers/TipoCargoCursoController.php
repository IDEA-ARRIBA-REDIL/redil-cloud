<?php

namespace App\Http\Controllers;

use App\Models\TipoCargoCurso;
use Illuminate\Http\Request;

class TipoCargoCursoController extends Controller
{
    public function index()
    {
        $cargos = TipoCargoCurso::all();
        $carreras = \App\Models\Carrera::orderBy('nombre')->get();

        return view('contenido.paginas.cursos.tipos-cargo.listar', compact('cargos', 'carreras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        TipoCargoCurso::create([
            'nombre' => $request->nombre,
            'puede_responder_preguntas' => $request->has('puede_responder_preguntas'),
            'puede_editar_curso' => $request->has('puede_editar_curso'),
            'puede_editar_restricciones' => $request->has('puede_editar_restricciones'),
            'puede_editar_contenido' => $request->has('puede_editar_contenido'),
            'puede_gestionar_equipo' => $request->has('puede_gestionar_equipo'),
            'puede_gestionar_estudiantes' => $request->has('puede_gestionar_estudiantes'),
            'limita_carreras' => $request->has('limita_carreras'),
            'carreras_permitidas' => $request->carreras ?? [],
            'puede_ver_todos_los_cursos' => $request->has('puede_ver_todos_los_cursos'),
        ]);

        return redirect()->route('cursos.tipos-cargo.index')->with('success', 'Tipo de cargo creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $cargo = TipoCargoCurso::findOrFail($id);

        $cargo->update([
            'nombre' => $request->nombre,
            'puede_responder_preguntas' => $request->has('puede_responder_preguntas'),
            'puede_editar_curso' => $request->has('puede_editar_curso'),
            'puede_editar_restricciones' => $request->has('puede_editar_restricciones'),
            'puede_editar_contenido' => $request->has('puede_editar_contenido'),
            'puede_gestionar_equipo' => $request->has('puede_gestionar_equipo'),
            'puede_gestionar_estudiantes' => $request->has('puede_gestionar_estudiantes'),
            'limita_carreras' => $request->has('limita_carreras'),
            'carreras_permitidas' => $request->carreras ?? [],
            'puede_ver_todos_los_cursos' => $request->has('puede_ver_todos_los_cursos'),
        ]);

        return redirect()->route('cursos.tipos-cargo.index')->with('success', 'Tipo de cargo actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $cargo = TipoCargoCurso::findOrFail($id);

        if ($cargo->asignaciones()->count() > 0) {
            return redirect()->route('cursos.tipos-cargo.index')->with('error', 'No se puede eliminar el cargo porque tiene usuarios asignados.');
        }

        $cargo->delete();

        return redirect()->route('cursos.tipos-cargo.index')->with('success', 'Tipo de cargo eliminado exitosamente.');
    }
}
