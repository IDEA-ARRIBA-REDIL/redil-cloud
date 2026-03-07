<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use App\Models\CursoUsuarioCargo;

class CursoController extends Controller
{
    public function index()
    {
        return view('contenido.paginas.cursos.gestionar-cursos');
    }

    public function campus()
    {
        return view('contenido.paginas.cursos.catalogo');
    }

    public function checkout()
    {
        return view('contenido.paginas.cursos.checkout');
    }

    public function carrito()
    {
        return view('contenido.paginas.cursos.carrito');
    }

    public function compraFinalizada(\App\Models\CarritoCursoUser $carrito)
    {
        return view('contenido.paginas.cursos.compra-finalizada', compact('carrito'));
    }

    public function crear()
    {
        return view('contenido.paginas.cursos.crear-curso');
    }

    public function editar(Curso $curso)
    {
        return view('contenido.paginas.cursos.editar-curso', compact('curso'));
    }

    public function restricciones(Curso $curso)
    {
        return view('contenido.paginas.cursos.restricciones', compact('curso'));
    }

    public function detalle(Curso $curso)
    {
        return view('contenido.paginas.cursos.gestionar-detalle', compact('curso'));
    }

    public function contenido(Curso $curso)
    {
        return view('contenido.paginas.cursos.gestionar-contenido', compact('curso'));
    }

    public function actualizarDescripcion(Request $request, Curso $curso)
    {
        $request->validate([
            'descripcion_larga' => 'nullable|string',
            'mensaje_bienvenida' => 'nullable|string',
            'mensaje_aprobacion' => 'nullable|string',
        ]);

        $curso->update([
            'descripcion_larga' => $request->descripcion_larga,
            'mensaje_bienvenida' => $request->mensaje_bienvenida,
            'mensaje_aprobacion' => $request->mensaje_aprobacion,
        ]);


        return redirect()->route('cursos.detalle', $curso)->with('success', 'Descripción actualizada correctamente.');
    }

    public function inscritos(Curso $curso)
    {
        $configuracion = \App\Models\Configuracion::first();
        return view('contenido.paginas.cursos.gestionar-estudiantes', compact('curso', 'configuracion'));
    }

    // --- GESTIÓN DE EQUIPO DEL CURSO ---

    // --- RUTAS PÚBLICAS / FRONT-END ---

    /**
     * Muestra la vista de detalle público de un curso.
     *
     * @param string $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function previsualizar($slug)
    {
        // Buscar el curso por su slug, asegurarse de que esté activo (estado = 1), y cargar relaciones básicas
        $curso = Curso::with(['equipo.user', 'equipo.tipoCargo', 'aprendizajes', 'pasosRequisito', 'tareasRequisito', 'rangosEdad', 'estadosCiviles', 'categorias'])
            ->where('slug', $slug)
            ->where('estado', 'Publicado')
            ->firstOrFail();

        // Obtener la configuración general para los logos y URLs base de imágenes
        $configuracion = \App\Models\Configuracion::first();

        return view('contenido.paginas.cursos.previsualizar', compact('curso', 'configuracion'));
    }

    // --- GESTIÓN DE EQUIPO ---

    public function equipo(Curso $curso)
    {
        $equipo = $curso->equipo()->with(['user', 'tipoCargo'])->paginate(15);
        $tiposCargo = \App\Models\TipoCargoCurso::all();
        $configuracion = \App\Models\Configuracion::find(1); // Requisito para las rutas de imágenes

        return view('contenido.paginas.cursos.gestionar-equipo', compact('curso', 'equipo', 'tiposCargo', 'configuracion'));
    }

    public function guardarEquipo(Request $request, Curso $curso)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'tipo_cargo_curso_id' => 'required|exists:tipos_cargo_cursos,id',
            'activo' => 'boolean',
        ]);

        // Verificar si ya existe esta combinación
        $existe = CursoUsuarioCargo::where('curso_id', $curso->id)
            ->where('usuario_id', $request->usuario_id)
            ->where('tipo_cargo_curso_id', $request->tipo_cargo_curso_id)
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', 'El usuario ya tiene asignado este cargo en el curso.');
        }

        CursoUsuarioCargo::create([
            'curso_id' => $curso->id,
            'usuario_id' => $request->usuario_id,
            'tipo_cargo_curso_id' => $request->tipo_cargo_curso_id,
            'activo' => $request->input('activo', 1),
        ]);

        return redirect()->back()->with('success', 'Miembro del equipo asignado correctamente.');
    }

    public function activarEquipo(\App\Models\CursoUsuarioCargo $miembro)
    {
        $miembro->update(['activo' => true]);
        return redirect()->back()->with('success', 'Miembro activado correctamente.');
    }

    public function desactivarEquipo(\App\Models\CursoUsuarioCargo $miembro)
    {
        $miembro->update(['activo' => false]);
        return redirect()->back()->with('success', 'Miembro desactivado correctamente.');
    }

    public function eliminarEquipo(Request $request)
    {
        $request->validate([
            'miembro_id' => 'required|exists:curso_usuario_cargo,id',
        ]);

        $miembro = CursoUsuarioCargo::findOrFail($request->miembro_id);
        $miembro->delete();

        return redirect()->back()->with('success', 'Miembro del equipo removido del curso correctamente.');
    }

    // --- PANEL DE MODERACIÓN DE FORO (ASESOR) ---
    public function foro()
    {
        return view('contenido.paginas.cursos.foro');
    }
}
