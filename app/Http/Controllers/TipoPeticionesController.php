<?php

namespace App\Http\Controllers;

use App\Models\TipoPeticion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TipoPeticionesController extends Controller
{

    public function listar(Request $request): \Illuminate\View\View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // verificar si cumple el permiso
        $rolActivo->verificacionDelPermiso('configuraciones.subitem_tipo_de_peticiones');
        
        $buscar = $request->input('buscar');

        $tiposPeticiones = TipoPeticion::query()
        ->when($buscar, function ($query, $buscar) {
            return $query->where('nombre', 'ILIKE', '%'.$buscar.'%');
        })
        ->orderBy('orden', 'asc')
        ->paginate(12);

        return view('contenido.paginas.tipo-peticiones.listar', [
            'tiposPeticiones' => $tiposPeticiones,
            'buscar' => $buscar,
        ]);
    }

    public function nueva(): \Illuminate\View\View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // verificar si cumple el permiso
        $rolActivo->verificacionDelPermiso('configuraciones.subitem_tipo_de_peticiones');
        
        return view('contenido.paginas.tipo-peticiones.crear');
    }

    public function crear(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'orden' => 'required|integer',
            'mensaje_parte_1' => 'nullable|string',
            'mensaje_parte_2' => 'nullable|string',
            'banner_email_recortado' => 'nullable|string',
            'json_versiculos' => 'nullable|string',
        ]);

        $tipoPeticion = TipoPeticion::create($request->except(['banner_email_recortado', 'versiculos_peticion']));

        if ($request->filled('banner_email_recortado')) {
            $nombreBanner = 'banner-email-'.$tipoPeticion->id.'.png';
            $directorio = 'img/email/peticiones';

            $imageData = $request->input('banner_email_recortado');
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $imageData = base64_decode($imageData);

            Storage::put($directorio.'/'.$nombreBanner, $imageData);

            $tipoPeticion->banner_email = $nombreBanner;
            $tipoPeticion->save();
        }

        return redirect()->route('tipo-peticiones.listar')
            ->with('success', 'Tipo de Petición creado correctamente.');
    }

    public function editar(TipoPeticion $tipoPeticion): \Illuminate\View\View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // verificar si cumple el permiso
        $rolActivo->verificacionDelPermiso('configuraciones.subitem_tipo_de_peticiones');
        return view('contenido.paginas.tipo-peticiones.editar', compact('tipoPeticion'));
    }

    public function actualizar(Request $request, TipoPeticion $tipoPeticion): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'orden' => 'required|integer',
            'mensaje_parte_1' => 'nullable|string',
            'mensaje_parte_2' => 'nullable|string',
            'banner_email_recortado' => 'nullable|string',
            'json_versiculos' => 'nullable|string',
        ]);

        $tipoPeticion->update($request->except(['banner_email_recortado', 'versiculos_peticion']));

        if ($request->filled('banner_email_recortado')) {
            $nombreBanner = 'banner-email-'.$tipoPeticion->id.'.png';
            $directorio = 'img/email/peticiones';

            // Eliminar anterior si existe
            if ($tipoPeticion->banner_email) {
                if (Storage::exists($directorio.'/'.$tipoPeticion->banner_email)) {
                    Storage::delete($directorio.'/'.$tipoPeticion->banner_email);
                }
            }

            $imageData = $request->input('banner_email_recortado');
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $imageData = base64_decode($imageData);

            Storage::put($directorio.'/'.$nombreBanner, $imageData);

            $tipoPeticion->banner_email = $nombreBanner;
            $tipoPeticion->save();
        }

        return redirect()->route('tipo-peticiones.listar')
            ->with('success', 'Tipo de Petición actualizado correctamente.');
    }

    public function eliminar(TipoPeticion $tipoPeticion): \Illuminate\Http\RedirectResponse
    {
        $directorio = 'img/email/peticiones';

        // Eliminar banner si existe
        if ($tipoPeticion->banner_email) {
            if (Storage::exists($directorio.'/'.$tipoPeticion->banner_email)) {
                Storage::delete($directorio.'/'.$tipoPeticion->banner_email);
            }
        }

        $tipoPeticion->delete();

        return redirect()->route('tipo-peticiones.listar')
            ->with('success', 'Tipo de Petición eliminado correctamente.');
    }
}
