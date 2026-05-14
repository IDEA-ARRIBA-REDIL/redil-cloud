<?php

namespace App\Http\Controllers;

use App\Models\VersiculoDiario;
use Illuminate\Http\Request;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Auth;

class VersiculoDiarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.subitem_gestionar_versiculos');

        $fechaInicio = $request->get('fecha_inicio', now()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->addDays(30)->format('Y-m-d'));

        $versiculos = VersiculoDiario::whereBetween('fecha_publicacion', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_publicacion', 'desc')
            ->paginate(15);

        return view('contenido.paginas.versiculos.gestionar', compact('versiculos', 'fechaInicio', 'fechaFin', 'rolActivo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.subitem_nuevo_versiculo');

        $fechasOcupadas = VersiculoDiario::pluck('fecha_publicacion')->map(function ($date) {
            return $date->format('Y-m-d');
        })->toArray();

        return view('contenido.paginas.versiculos.crear', compact('fechasOcupadas', 'rolActivo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_publicacion' => 'required|date|unique:versiculos_diarios,fecha_publicacion',
            'version_uri' => 'required|string',
            'libro_nombre' => 'required|string',
            'cita_referencia' => 'required|string',
            'texto_versiculo' => 'nullable|string',
            'url_video_reflexion' => 'nullable|url',
        ]);

        $versiculo = new VersiculoDiario();
        $versiculo->fecha_publicacion = $request->fecha_publicacion;
        $versiculo->version_uri = $request->version_uri;
        $versiculo->libro_nombre = $request->libro_nombre;
        $versiculo->cita_referencia = $request->cita_referencia;

        // Decodificamos el JSON que viene del frontend
        $versiculo->texto_versiculo = json_decode($request->texto_versiculo, true);

        $versiculo->url_video_reflexion = $request->url_video_reflexion;
        $versiculo->usuario_id = auth()->id();

        // Manejo de la imagen
        if ($request->imagen_base64) {
            $imagenPartes = explode(';base64,', $request->imagen_base64);
            $imagenBase64 = base64_decode($imagenPartes[1]);
            $nombreFoto = 'versiculo-' . time() . '.jpg';
            $directorio = 'img/versiculo-diario/';

            \Illuminate\Support\Facades\Storage::disk()->put($directorio.$nombreFoto, $imagenBase64);
            $versiculo->ruta_imagen = $nombreFoto;
        }

        $versiculo->save();

        return redirect()->route('versiculos.index')->with('success', 'Versículo diario guardado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VersiculoDiario $versiculoDiario)
    {
        return view('contenido.paginas.versiculos.mostrar', compact('versiculoDiario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VersiculoDiario $versiculo)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.opcion_modificar_versiculo');

        $fechasOcupadas = VersiculoDiario::where('id', '!=', $versiculo->id)
            ->pluck('fecha_publicacion')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })->toArray();

        return view('contenido.paginas.versiculos.editar', compact('versiculo', 'fechasOcupadas', 'rolActivo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VersiculoDiario $versiculo)
    {
        $request->validate([
            'fecha_publicacion' => 'required|date|unique:versiculos_diarios,fecha_publicacion,' . $versiculo->id,
            'version_uri' => 'required|string',
            'libro_nombre' => 'required|string',
            'cita_referencia' => 'required|string',
            'texto_versiculo' => 'nullable|string',
            'url_video_reflexion' => 'nullable|url',
        ]);

        $versiculo->fecha_publicacion = $request->fecha_publicacion;
        $versiculo->version_uri = $request->version_uri;
        $versiculo->libro_nombre = $request->libro_nombre;
        $versiculo->cita_referencia = $request->cita_referencia;

        if ($request->texto_versiculo) {
            $versiculo->texto_versiculo = json_decode($request->texto_versiculo, true);
        }

        $versiculo->url_video_reflexion = $request->url_video_reflexion;

        // Manejo de la imagen
        if ($request->imagen_base64) {
            $nombreFoto = 'versiculo-' . time() . '.jpg';
            $directorio = 'img/versiculo-diario/';

            // Borrar imagen anterior si existe
            if ($versiculo->ruta_imagen) {
                \Illuminate\Support\Facades\Storage::disk()->delete($directorio.$versiculo->ruta_imagen);
            }

            $imagenPartes = explode(';base64,', $request->imagen_base64);
            $imagenBase64 = base64_decode($imagenPartes[1]);

            \Illuminate\Support\Facades\Storage::disk()->put($directorio.$nombreFoto, $imagenBase64);
            $versiculo->ruta_imagen = $nombreFoto;
        }

        $versiculo->save();

        return redirect()->route('versiculos.index')->with('success', 'Versículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VersiculoDiario $versiculo)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.opcion_eliminar_versiculo');

        // Borrado físico de la imagen si existe
        if ($versiculo->ruta_imagen) {
            \Illuminate\Support\Facades\Storage::disk()->delete('img/versiculo-diario/' . $versiculo->ruta_imagen);
        }

        $versiculo->delete();

        return redirect()->route('versiculos.index')->with('success', 'Versículo eliminado correctamente junto con su imagen.');
    }
}
