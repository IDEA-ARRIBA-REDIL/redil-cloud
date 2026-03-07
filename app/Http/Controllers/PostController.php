<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\EstadoCivil;
use App\Models\RangoEdad;
use App\Models\TipoUsuario;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Muestra la lista de publicaciones para administración.
     */
    public function gestionar(Request $request)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('posts.subitem_gestionar_publicaciones');
        
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->addDays(30)->format('Y-m-d'));

        $posts = Post::with('user')
            ->where(function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                      ->orWhere('visualizar_siempre', true);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(15);

        return view('contenido.paginas.posts.gestionar', compact('posts', 'configuracion', 'fechaInicio', 'fechaFin', 'rolActivo'));
    }

    /**
     * Muestra el formulario para crear una nueva publicación.
     */
    public function crear()
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('posts.subitem_nueva_publicacion');

        $sedes = Sede::all();
        $estadosCiviles = EstadoCivil::all();
        $rangosEdad = RangoEdad::all();
        $tiposUsuario = TipoUsuario::all();
        $pasosCrecimiento = PasoCrecimiento::all();
        $estadosPasos = EstadoPasoCrecimientoUsuario::all();
        $tareasConsolidacion = TareaConsolidacion::all();
        $estadosTareas = EstadoTareaConsolidacion::all();

        return view('contenido.paginas.posts.crear', compact(
            'configuracion', 
            'rolActivo', 
            'sedes', 
            'estadosCiviles', 
            'rangosEdad', 
            'tiposUsuario', 
            'pasosCrecimiento', 
            'estadosPasos', 
            'tareasConsolidacion', 
            'estadosTareas'
        ));
    }

    /**
     * Almacena una nueva publicación.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'visualizar_siempre' => 'boolean',
            'imagen' => 'nullable|image|max:2048',
            'visible_todos' => 'nullable',
            'genero' => 'nullable|integer',
            'sedes' => 'nullable|array',
            'estadosCiviles' => 'nullable|array',
            'rangosEdad' => 'nullable|array',
            'tiposUsuario' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $post = new Post();
            $post->user_id = auth()->id();
            $post->descripcion = $request->descripcion;
            $post->fecha_inicio = $request->fecha_inicio;
            $post->fecha_fin = $request->fecha_fin;
            $post->visualizar_siempre = $request->has('visualizar_siempre');
            $post->visible_todos = $request->has('visible_todos');
            $post->genero = $request->genero ?? 3;

            // Manejo de la imagen base64 (recortada)
            if ($request->imagen_base64) {
                $configuracion = Configuracion::find(1);
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/publicaciones/');
                
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                $imagenPartes = explode(';base64,', $request->imagen_base64);
                $imagenBase64 = base64_decode($imagenPartes[1]);
                $nombreFoto = 'post-' . time() . '.jpg';
                $imagenPath = $path . $nombreFoto;
                
                file_put_contents($imagenPath, $imagenBase64);
                $post->image_path = $nombreFoto;
            }

            $post->save();

            // Guardar restricciones
            if (!$post->visible_todos) {
                $post->sedes()->sync($request->sedes);
                $post->estadosCiviles()->sync($request->estadosCiviles);
                $post->rangosEdad()->sync($request->rangosEdad);
                $post->tiposUsuarios()->sync($request->tiposUsuario);

                // Procesos Requisito
                if ($request->has('pasos')) {
                    $pasosData = [];
                    foreach ($request->pasos as $index => $paso) {
                        if ($paso['id'] && $paso['estado']) {
                            // Cambiamos sync a coleccionar un array numerado o multiple para attach
                            $post->procesosRequisito()->attach($paso['id'], [
                                'estado_paso_crecimiento_usuario_id' => $paso['estado'],
                                'indice' => $index
                            ]);
                        }
                    }
                }

                // Tareas Requisito
                if ($request->has('tareas')) {
                    $tareasData = [];
                    foreach ($request->tareas as $index => $tarea) {
                        if ($tarea['id'] && $tarea['estado']) {
                            $post->tareasRequisito()->attach($tarea['id'], [
                                'estado_tarea_consolidacion_id' => $tarea['estado'],
                                'indice' => $index
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('posts.gestionar')->with('success', 'Publicación creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la publicación: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra el formulario para editar una publicación.
     */
    public function edit(Post $post)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('posts.opcion_modificar_publicacion');

        $sedes = Sede::all();
        $estadosCiviles = EstadoCivil::all();
        $rangosEdad = RangoEdad::all();
        $tiposUsuario = TipoUsuario::all();
        $pasosCrecimiento = PasoCrecimiento::all();
        $estadosPasos = EstadoPasoCrecimientoUsuario::all();
        $tareasConsolidacion = TareaConsolidacion::all();
        $estadosTareas = EstadoTareaConsolidacion::all();

        // Obtener IDs de relaciones actuales para preseleccionar en la vista
        $sedesPost = $post->sedes->pluck('id')->toArray();
        $estadosCivilesPost = $post->estadosCiviles->pluck('id')->toArray();
        $rangosEdadPost = $post->rangosEdad->pluck('id')->toArray();
        $tiposUsuarioPost = $post->tiposUsuarios->pluck('id')->toArray();
        $pasosPost = $post->procesosRequisito;
        $tareasPost = $post->tareasRequisito;

        return view('contenido.paginas.posts.editar', compact(
            'post', 
            'configuracion', 
            'rolActivo',
            'sedes', 
            'estadosCiviles', 
            'rangosEdad', 
            'tiposUsuario', 
            'pasosCrecimiento', 
            'estadosPasos', 
            'tareasConsolidacion', 
            'estadosTareas',
            'sedesPost',
            'estadosCivilesPost',
            'rangosEdadPost',
            'tiposUsuarioPost',
            'pasosPost',
            'tareasPost'
        ));
    }

    /**
     * Actualiza una publicación.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'visualizar_siempre' => 'boolean',
            'visible_todos' => 'nullable',
            'genero' => 'nullable|integer',
            'sedes' => 'nullable|array',
            'estadosCiviles' => 'nullable|array',
            'rangosEdad' => 'nullable|array',
            'tiposUsuario' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $post->descripcion = $request->descripcion;
            $post->fecha_inicio = $request->fecha_inicio;
            $post->fecha_fin = $request->fecha_fin;
            $post->visualizar_siempre = $request->has('visualizar_siempre');
            $post->visible_todos = $request->has('visible_todos');
            $post->genero = $request->genero ?? 3;

            // Manejo de la imagen base64 (recortada)
            if ($request->imagen_base64) {
                $configuracion = Configuracion::find(1);
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/publicaciones/');
                
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                // Borrar imagen anterior si existe
                if ($post->image_path) {
                    $oldPath = $path . $post->image_path;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $imagenPartes = explode(';base64,', $request->imagen_base64);
                $imagenBase64 = base64_decode($imagenPartes[1]);
                $nombreFoto = 'post-' . time() . '.jpg';
                $imagenPath = $path . $nombreFoto;
                
                file_put_contents($imagenPath, $imagenBase64);
                $post->image_path = $nombreFoto;
            }

            $post->save();

            // Actualizar restricciones
            if (!$post->visible_todos) {
                $post->sedes()->sync($request->sedes);
                $post->estadosCiviles()->sync($request->estadosCiviles);
                $post->rangosEdad()->sync($request->rangosEdad);
                $post->tiposUsuarios()->sync($request->tiposUsuario);

                // Limpiar previamente las relaciones pivot anidadas para reemplazarlas
                $post->procesosRequisito()->detach();
                $post->tareasRequisito()->detach();

                // Procesos Requisito
                if ($request->has('pasos')) {
                    foreach ($request->pasos as $index => $paso) {
                        if ($paso['id'] && $paso['estado']) {
                            $post->procesosRequisito()->attach($paso['id'], [
                                'estado_paso_crecimiento_usuario_id' => $paso['estado'],
                                'indice' => $index
                            ]);
                        }
                    }
                }

                // Tareas Requisito
                if ($request->has('tareas')) {
                    foreach ($request->tareas as $index => $tarea) {
                        if ($tarea['id'] && $tarea['estado']) {
                            $post->tareasRequisito()->attach($tarea['id'], [
                                'estado_tarea_consolidacion_id' => $tarea['estado'],
                                'indice' => $index
                            ]);
                        }
                    }
                }
            } else {
                // Si es visible para todos, limpiar restricciones
                $post->sedes()->detach();
                $post->estadosCiviles()->detach();
                $post->rangosEdad()->detach();
                $post->tiposUsuarios()->detach();
                $post->procesosRequisito()->detach();
                $post->tareasRequisito()->detach();
            }

            DB::commit();
            return redirect()->route('posts.gestionar')->with('success', 'Publicación actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la publicación: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Elimina una publicación.
     */
    public function destroy(Post $post)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('posts.opcion_eliminar_publicacion');
        
        if ($post->image_path) {
            $configuracion = Configuracion::find(1);
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/publicaciones/' . $post->image_path);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $post->delete();

        return redirect()->route('posts.gestionar')->with('success', 'Publicación eliminada correctamente.');
    }
}
