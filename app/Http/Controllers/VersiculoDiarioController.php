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
        $configuracion = \App\Models\Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.subitem_gestionar_versiculos');
        
        $fechaInicio = $request->get('fecha_inicio', now()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->addDays(30)->format('Y-m-d'));

        $versiculos = VersiculoDiario::whereBetween('fecha_publicacion', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_publicacion', 'desc')
            ->paginate(15);

        return view('contenido.paginas.versiculos.gestionar', compact('versiculos', 'configuracion', 'fechaInicio', 'fechaFin', 'rolActivo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.subitem_nuevo_versiculo');

        $fechasOcupadas = VersiculoDiario::pluck('fecha_publicacion')->map(function ($date) {
            return $date->format('Y-m-d');
        })->toArray();
        
        return view('contenido.paginas.versiculos.crear', compact('configuracion', 'fechasOcupadas', 'rolActivo'));
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
            $configuracion = Configuracion::find(1);
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/versiculo-diario/');
            
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $imagenPartes = explode(';base64,', $request->imagen_base64);
            $imagenBase64 = base64_decode($imagenPartes[1]);
            $nombreFoto = 'versiculo-' . time() . '.jpg';
            $imagenPath = $path . $nombreFoto;
            
            file_put_contents($imagenPath, $imagenBase64);
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
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.opcion_modificar_versiculo');

        $fechasOcupadas = VersiculoDiario::where('id', '!=', $versiculo->id)
            ->pluck('fecha_publicacion')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })->toArray();

        return view('contenido.paginas.versiculos.editar', compact('versiculo', 'configuracion', 'fechasOcupadas', 'rolActivo'));
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
            $configuracion = Configuracion::find(1);
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/versiculo-diario/');
            
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            // Borrar imagen anterior si existe
            if ($versiculo->ruta_imagen) {
                $oldPath = $path . $versiculo->ruta_imagen;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $imagenPartes = explode(';base64,', $request->imagen_base64);
            $imagenBase64 = base64_decode($imagenPartes[1]);
            $nombreFoto = 'versiculo-' . time() . '.jpg';
            $imagenPath = $path . $nombreFoto;
            
            file_put_contents($imagenPath, $imagenBase64);
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
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('versiculos.opcion_eliminar_versiculo');

        // Borrado físico de la imagen si existe
        if ($versiculo->ruta_imagen) {
            $configuracion = Configuracion::find(1);
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/versiculo-diario/' . $versiculo->ruta_imagen);
            
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $versiculo->delete();

        return redirect()->route('versiculos.index')->with('success', 'Versículo eliminado correctamente junto con su imagen.');
    }
}
