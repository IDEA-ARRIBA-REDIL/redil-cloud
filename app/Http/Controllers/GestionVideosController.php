<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GestionVideo;
use App\Models\Configuracion;

class GestionVideosController extends Controller
{
  public function listarVideos(Request $request)
    {
      $query = GestionVideo::query();

      // Buscador
      if ($request->has('buscar') && $request->buscar != '') {
        $query->where('nombre', 'like', '%' . $request->buscar . '%');
      }

      $videos = $query->orderBy('created_at', 'desc')->paginate(12);

      // Obtenemos configuración si tu layout lo requiere
      $configuracion = Configuracion::first();

      return view('contenido.paginas.gestion-videos.listar-videos', compact('videos', 'configuracion'));
    }

    public function crearVideos(Request $request)
    {
      $request->validate([
        'nombre' => 'required|string|max:255',
        'url_video' => 'required|string',
        'fecha_publicacion' => 'required|date',
        'visible' => 'required|boolean',
      ]);

      GestionVideo::create([
        'nombre' => $request->nombre,
        'url_video' => $request->url_video,
        'fecha_publicacion' => $request->fecha_publicacion,
        'visible' => $request->visible,
      ]);

      return redirect()->route('gestion-videos.listarVideos')->with('success', 'Video creado correctamente.');
    }

    public function actualizarVideos(Request $request, $id)
    {
      $video = GestionVideo::findOrFail($id);

      $request->validate([
        'nombre' => 'required|string|max:255',
        'url_video' => 'required|string',
        'fecha_publicacion' => 'required|date',
        'visible' => 'required|boolean',
      ]);

      $video->update([
        'nombre' => $request->nombre,
        'url_video' => $request->url_video,
        'fecha_publicacion' => $request->fecha_publicacion,
        'visible' => $request->visible,
      ]);

      return redirect()->route('gestion-videos.listarVideos')->with('success', 'Video actualizado correctamente.');
    }

    public function eliminarVideo($id)
    {
      $video = GestionVideo::findOrFail($id);
      $video->delete();

      return redirect()->route('gestion-videos.listarVideos')->with('success', 'Video eliminado correctamente.');
    }
}
